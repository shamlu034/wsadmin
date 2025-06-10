INSERT INTO admin_users (id, username, password, name, avatar, remember_token, created_at, updated_at) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'avatar1.jpg', 'token123', '2024-06-10 21:30:00', '2024-06-10 21:30:00'),
(2, 'editor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Editor User', NULL, NULL, '2024-06-10 21:31:00', '2024-06-10 21:31:00'),
(3, 'viewer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Viewer User', 'avatar2.jpg', 'token456', '2024-06-10 21:32:00', '2024-06-10 21:32:00');
INSERT INTO admin_roles (id, name, slug, created_at, updated_at) VALUES
(1, 'Administrator', 'admin', '2024-06-10 21:30:00', '2024-06-10 21:30:00'),
(2, 'Editor', 'editor', '2024-06-10 21:31:00', '2024-06-10 21:31:00'),
(3, 'Viewer', 'viewer', '2024-06-10 21:32:00', '2024-06-10 21:32:00');
INSERT INTO admin_permissions (id, name, slug, http_method, http_path, created_at, updated_at) VALUES
(1, 'All Access', 'all-access', NULL, '*', '2024-06-10 21:30:00', '2024-06-10 21:30:00'),
(2, 'Edit Content', 'edit-content', 'POST,PUT', '/content/*', '2024-06-10 21:31:00', '2024-06-10 21:31:00'),
(3, 'View Reports', 'view-reports', 'GET', '/reports', '2024-06-10 21:32:00', '2024-06-10 21:32:00');
INSERT INTO admin_menu
(id, parent_id, `order`, title, icon, uri, permission, created_at, updated_at) VALUES
(8, 0, 8, '用户管理', 'fa-users', 'user', NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(10, 8, 10, '会员用户', 'fa-user-plus', 'user_vip', NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(11, 8, 11, '实名认证审核', 'fa-id-card', 'user_card', NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(12, 8, 12, '会员开通记录', 'fa-history', 'vip_log', NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(13, 0, 13, '视频管理', 'fa-video', NULL, NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(14, 13, 14, '视频列表', 'fa-film', 'video', NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(15, 13, 15, '视频分类', 'fa-folder', 'video_nation', NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(16, 13, 16, '视频标签', 'fa-tags', 'video_tag', NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(17, 13, 17, '视频大赛', 'fa-trophy', 'video_contest', NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(18, 13, 18, '大赛 Banner', 'fa-image', 'contest_banner', NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(19, 0, 19, '财务管理', 'fa-money', NULL, NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(20, 19, 20, '银行卡管理', 'fa-credit-card', 'bank', NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(21, 19, 21, '充提设置', 'fa-exchange', 'exchange', NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(22, 19, 22, '充提列表', 'fa-list', 'withdraw', NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(23, 0, 23, '签到管理', 'fa-calendar-check', NULL, NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(24, 23, 24, '签到设置', 'fa-cog', 'check_in', NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(25, 23, 25, '签到记录', 'fa-list-alt', 'check_log', NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(26, 0, 26, 'VIP 管理', 'fa-star', 'vip', NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(27, 0, 27, '客服管理', 'fa-headset', NULL, NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(28, 27, 28, '客服设置', 'fa-cogs', 'setting_chat', NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(29, 27, 29, '客服列表', 'fa-user-headset', 'customer', NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00'),
(30, 0, 30, '鱼苗管理', 'fa-fish', 'fry', NULL, '2025-06-10 23:28:00', '2025-06-10 23:28:00');
INSERT INTO admin_role_users (role_id, user_id, created_at, updated_at) VALUES
(1, 1, '2024-06-10 21:30:00', '2024-06-10 21:30:00'),
(2, 2, '2024-06-10 21:31:00', '2024-06-10 21:31:00'),
(3, 3, '2024-06-10 21:32:00', '2024-06-10 21:32:00');
INSERT INTO admin_role_permissions (role_id, permission_id, created_at, updated_at) VALUES
(1, 1, '2024-06-10 21:30:00', '2024-06-10 21:30:00'),
(2, 2, '2024-06-10 21:31:00', '2024-06-10 21:31:00'),
(3, 3, '2024-06-10 21:32:00', '2024-06-10 21:32:00');
INSERT INTO admin_user_permissions (user_id, permission_id, created_at, updated_at) VALUES
(2, 3, '2024-06-10 21:31:00', '2024-06-10 21:31:00');
INSERT INTO admin_role_menu (role_id, menu_id, created_at, updated_at) VALUES
(1, 1, '2024-06-10 21:30:00', '2024-06-10 21:30:00'),
(1, 2, '2024-06-10 21:30:00', '2024-06-10 21:30:00'),
(2, 3, '2024-06-10 21:31:00', '2024-06-10 21:31:00'),
(3, 4, '2024-06-10 21:32:00', '2024-06-10 21:32:00');
INSERT INTO admin_operation_log (id, user_id, path, method, ip, input, created_at, updated_at) VALUES
(1, 1, '/dashboard', 'GET', '192.168.1.1', '{"action":"view"}', '2024-06-10 21:30:00', '2024-06-10 21:30:00'),
(2, 2, '/content/edit', 'POST', '192.168.1.2', '{"title":"New Post"}', '2024-06-10 21:31:00', '2024-06-10 21:31:00'),
(3, 3, '/reports', 'GET', '192.168.1.3', '{"filter":"monthly"}', '2024-06-10 21:32:00', '2024-06-10 21:32:00');

INSERT INTO failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) VALUES
(1, '550e8400-e29b-41d4-a716-446655440000', 'redis', 'default', '{"job":"App\\\\Jobs\\\\ProcessOrder","data":{"order_id":123}}', 'Exception: Order not found in App\\Jobs\\ProcessOrder:32', '2024-06-10 21:40:00'),
(2, '6ba7b810-9dad-11d1-80b4-00c04fd430c8', 'database', 'emails', '{"job":"App\\\\Jobs\\\\SendEmail","data":{"email":"user@example.com"}}', 'Error: Email server timeout in App\\Jobs\\SendEmail:45', '2024-06-10 21:41:00'),
(3, '7c4a8d09-ca76-43fb-95c2-1d1e0e6294b7', 'redis', 'notifications', '{"job":"App\\\\Jobs\\\\SendNotification","data":{"user_id":456}}', 'RuntimeException: Invalid user ID in App\\Jobs\\SendNotification:19', '2024-06-10 21:42:00');
INSERT INTO personal_access_tokens (id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at) VALUES
(1, 'App\\Models\\User', 1, 'auth_token_john', '1a2b3c4d5e6f7g8h9i0j1k2l3m4n5o6p7q8r9s0t1u2v3w4x5y6z7a8b9c0d1e2', '["read","write"]', '2024-06-10 21:45:00', NULL, '2024-06-10 21:40:00', '2024-06-10 21:45:00'),
(2, 'App\\Models\\User', 2, 'api_token_jane', '2b3c4d5e6f7g8h9i0j1k2l3m4n5o6p7q8r9s0t1u2v3w4x5y6z7a8b9c0d1e2f3', '["read"]', NULL, '2024-06-17 21:40:00', '2024-06-10 21:41:00', '2024-06-10 21:41:00'),
(3, 'App\\Models\\User', 3, 'mobile_token_alice', '3c4d5e6f7g8h9i0j1k2l3m4n5o6p7q8r9s0t1u2v3w4x5y6z7a8b9c0d1e2f3g4', '["read","notifications"]', '2024-06-10 21:42:00', NULL, '2024-06-10 21:42:00', '2024-06-10 21:42:00');
