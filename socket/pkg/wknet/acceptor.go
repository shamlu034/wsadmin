//go:build linux || freebsd || dragonfly || darwin
// +build linux freebsd dragonfly darwin

package wknet

import (
	"fmt"
	"net"
	"os"
	"strings"
	"sync"

	perrors "github.com/WuKongIM/WuKongIM/pkg/errors"
	"github.com/WuKongIM/WuKongIM/pkg/socket"
	"github.com/WuKongIM/WuKongIM/pkg/wklog"
	"github.com/WuKongIM/WuKongIM/pkg/wknet/netpoll"
	"go.uber.org/zap"
	"golang.org/x/sys/unix"
)

type Acceptor struct {
	reactorSubs       []*ReactorSub
	eg                *Engine
	listenPoller      *netpoll.Poller
	listenWSPoller    *netpoll.Poller
	listenWSSPoller   *netpoll.Poller
	listen            *listener
	listenWS          *listener // websocket
	listenWSS         *listener // websocket
	tcpRealListenAddr net.Addr  // tcp real listen addr
	wsRealListenAddr  net.Addr  // websocket real listen addr

	wklog.Log
}

func NewAcceptor(eg *Engine) *Acceptor {
	reactorSubs := make([]*ReactorSub, eg.options.SubReactorNum)
	for i := 0; i < eg.options.SubReactorNum; i++ {
		reactorSubs[i] = NewReactorSub(eg, i)
	}
	a := &Acceptor{
		eg:              eg,
		reactorSubs:     reactorSubs,
		listenPoller:    netpoll.NewPoller("listenerPoller"),
		listenWSPoller:  netpoll.NewPoller("listenWSPoller"),
		listenWSSPoller: netpoll.NewPoller("listenWSSPoller"),
		Log:             wklog.NewWKLog("Acceptor"),
	}

	return a
}

func (a *Acceptor) Start() error {

	return a.start()
}

func (a *Acceptor) start() error {

	for _, reactorSub := range a.reactorSubs {
		reactorSub.Start()
	}

	var wg = &sync.WaitGroup{}

	wg.Add(1)
	go func() {
		err := a.initTCPListener(wg)
		if err != nil {
			panic(err)
		}
	}()

	if strings.TrimSpace(a.eg.options.WsAddr) != "" {
		wg.Add(1)
		go func() {
			err := a.initWSListener(wg)
			if err != nil {
				panic(err)
			}
		}()
	}
	if strings.TrimSpace(a.eg.options.WssAddr) != "" {
		wg.Add(1)
		go func() {
			err := a.initWSSListener(wg)
			if err != nil {
				panic(err)
			}
		}()
	}

	wg.Wait()
	return nil
}

func (a *Acceptor) Stop() error {

	// -----------------listen-----------------
	err := a.listenPoller.Close()
	if err != nil {
		a.Warn("listenPoller.Close() failed", zap.Error(err))
	}
	if a.listen != nil {
		err = a.listen.Close()
		if err != nil {
			a.Warn("listen.Close() failed", zap.Error(err))
		}
	}

	// -----------------ws-----------------

	if a.listenWS != nil {
		err = a.listenWS.Close()
		if err != nil {
			a.Warn("listenWS.Close() failed", zap.Error(err))
		}
	}
	err = a.listenWSPoller.Close()
	if err != nil {
		a.Warn("listenWSPoller.Close() failed", zap.Error(err))
	}

	// -----------------wss-----------------
	err = a.listenWSSPoller.Close()
	if err != nil {
		a.Warn("listenWSSPoller.Close() failed", zap.Error(err))
	}
	if a.listenWSS != nil {
		err = a.listenWSS.Close()
		if err != nil {
			a.Warn("listenWSS.Close() failed", zap.Error(err))
		}
	}

	// -----------------reactor sub-----------------
	for _, reactorSub := range a.reactorSubs {
		reactorSub.Stop()
	}

	return nil
}

func (a *Acceptor) initTCPListener(wg *sync.WaitGroup) error {
	// tcp
	a.listen = newListener(a.eg.options.Addr, a.eg.options)
	err := a.listen.init()
	if err != nil {
		return err
	}
	a.tcpRealListenAddr = a.listen.realAddr
	if err := a.listenPoller.AddRead(a.listen.fd); err != nil {
		return fmt.Errorf("add listener fd to poller failed %s", err)
	}
	wg.Done()

	a.listenPoller.Polling(func(fd int, ev netpoll.PollEvent) error {
		return a.acceptConn(fd, false, false)
	})
	return nil

}

func (a *Acceptor) initWSListener(wg *sync.WaitGroup) error {
	// tcp
	a.listenWS = newListener(a.eg.options.WsAddr, a.eg.options)
	err := a.listenWS.init()
	if err != nil {
		return err
	}
	a.wsRealListenAddr = a.listenWS.realAddr
	if err := a.listenWSPoller.AddRead(a.listenWS.fd); err != nil {
		return fmt.Errorf("add ws listener fd to poller failed %s", err)
	}
	wg.Done()
	a.listenWSPoller.Polling(func(fd int, ev netpoll.PollEvent) error {
		return a.acceptConn(fd, true, false)
	})
	return nil
}

func (a *Acceptor) initWSSListener(wg *sync.WaitGroup) error {
	// tcp
	a.listenWSS = newListener(a.eg.options.WssAddr, a.eg.options)
	err := a.listenWSS.init()
	if err != nil {
		return err
	}
	a.wsRealListenAddr = a.listenWSS.realAddr
	if err := a.listenWSSPoller.AddRead(a.listenWSS.fd); err != nil {
		return fmt.Errorf("add ws listener fd to poller failed %s", err)
	}
	wg.Done()
	a.listenWSSPoller.Polling(func(fd int, ev netpoll.PollEvent) error {
		return a.acceptConn(fd, false, true)
	})
	return nil
}

func (a *Acceptor) acceptConn(listenFd int, ws bool, wss bool) error {
	var (
		conn Conn
		err  error
	)
	connFd, sa, err := unix.Accept(listenFd)
	if err != nil {
		if err == unix.EAGAIN {
			return nil
		}
		a.Error("Accept() failed", zap.Error(err))
		return perrors.ErrAcceptSocket
	}
	if err = os.NewSyscallError("fcntl nonblock", unix.SetNonblock(connFd, true)); err != nil {
		return err
	}
	remoteAddr := socket.SockaddrToTCPOrUnixAddr(sa)
	if a.eg.options.TCPKeepAlive > 0 && a.listen.customNetwork == "tcp" {
		err = socket.SetKeepAlivePeriod(connFd, int(a.eg.options.TCPKeepAlive.Seconds()))
		a.Error("SetKeepAlivePeriod() failed", zap.Error(err))
	}
	subReactor := a.reactorSubByConnFd(connFd)
	if wss {
		if conn, err = a.eg.eventHandler.OnNewWSSConn(a.eg.GenClientID(), newNetFd(connFd), a.wssRealAddr(), remoteAddr, a.eg, subReactor); err != nil {
			return err
		}
	} else if ws {
		if conn, err = a.eg.eventHandler.OnNewWSConn(a.eg.GenClientID(), newNetFd(connFd), a.wsRealAddr(), remoteAddr, a.eg, subReactor); err != nil {
			return err
		}
	} else {

		if conn, err = a.eg.eventHandler.OnNewConn(a.eg.GenClientID(), newNetFd(connFd), a.tcpRealAddr(), remoteAddr, a.eg, subReactor); err != nil {
			return err
		}
	}
	// add conn to sub reactor
	subReactor.AddConn(conn)
	// call on connect
	a.eg.eventHandler.OnConnect(conn)

	return nil
}

func (a *Acceptor) reactorSubByConnFd(connfd int) *ReactorSub {

	return a.reactorSubs[connfd%len(a.reactorSubs)]
}

func (a *Acceptor) tcpRealAddr() net.Addr {
	return a.listen.realAddr
}

func (a *Acceptor) wsRealAddr() net.Addr {
	return a.listenWS.realAddr
}

func (a *Acceptor) wssRealAddr() net.Addr {
	return a.listenWSS.realAddr
}
