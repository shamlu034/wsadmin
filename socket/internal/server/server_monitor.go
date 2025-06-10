package server

import (
	"fmt"
	"io/fs"
	"net/http"
	"strings"

	"github.com/WuKongIM/WuKongIM/pkg/wkhttp"
	"github.com/WuKongIM/WuKongIM/pkg/wklog"
	"github.com/WuKongIM/WuKongIM/version"
	"github.com/gin-contrib/gzip"
	"github.com/gin-gonic/gin"
	"go.uber.org/zap"
)

type MonitorServer struct {
	s *Server
	r *wkhttp.WKHttp
	wklog.Log
	addr string
}

func NewMonitorServer(s *Server) *MonitorServer {
	r := wkhttp.New()
	r.Use(wkhttp.CORSMiddleware())
	return &MonitorServer{
		addr: s.opts.Monitor.Addr,
		s:    s,
		r:    r,
		Log:  wklog.NewWKLog("MonitorServer"),
	}

}

func (m *MonitorServer) Start() {

	m.r.Use(func(c *wkhttp.Context) { // 管理者权限判断
		if c.FullPath() == "/api/varz" {
			c.Next()
			return
		}
		if strings.TrimSpace(m.s.opts.ManagerToken) == "" {
			c.Next()
			return
		}
		if strings.HasPrefix(c.FullPath(), "/web") {
			c.Next()
			return
		}

		managerToken := c.GetHeader("token")
		if managerToken != m.s.opts.ManagerToken {
			c.AbortWithStatus(http.StatusUnauthorized)
			return
		}
		c.Next()
	})

	m.r.Use(func(c *wkhttp.Context) { // ip黑名单判断
		clientIP := c.Request.Header.Get("X-Forwarded-For")
		if strings.TrimSpace(clientIP) == "" {
			clientIP = c.ClientIP()
		}
		if !m.s.AllowIP(clientIP) {
			c.AbortWithStatus(http.StatusForbidden)
			return
		}
		c.Next()
	})

	m.r.GetGinRoute().Use(gzip.Gzip(gzip.DefaultCompression))

	st, _ := fs.Sub(version.WebFs, "web/dist")
	m.r.GetGinRoute().NoRoute(func(c *gin.Context) {
		if strings.HasPrefix(c.Request.URL.Path, "/web") {
			c.FileFromFS("./index.html", http.FS(st))
			return
		}
	})

	m.r.GetGinRoute().StaticFS("/web", http.FS(st))

	// 监控api
	monitorapi := NewMonitorAPI(m.s)
	monitorapi.Route(m.r)

	go func() {
		err := m.r.Run(m.addr) // listen and serve
		if err != nil {
			panic(err)
		}
	}()
	m.Info("MonitorServer started", zap.String("addr", m.addr))

	_, port := parseAddr(m.addr)
	m.Info(fmt.Sprintf("Monitor web address： http://localhost:%d/web", port))
}

func (m *MonitorServer) Stop() error {

	return nil
}
