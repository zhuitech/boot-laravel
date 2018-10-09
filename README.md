# Laraboot
- 添加一些常用基础扩展
- 微服务间共用的类库

### 安装
在项目根目录下运行如下composer命令:
```php
// 推荐
composer require trackhub/laraboot

// 安装开发中版本
composer require trackhub/laraboot:dev-dev
```

### 配置内部功能
为了灵活和速度，这里不直接依赖第三方包，在需要使用对应功能的时候，请添加第三方包。本模块会自动配置已经安装的第三方包。
```php
// 中国行政区
composer require yingouqlj/china-region

// 文件上传
composer require overtrue/laravel-uploader

// 消息提示框
composer require laracasts/flash
```

### 常用命令
更新IDE提示
```php
// 更新Facade等提示
php artisan ide-helper:generate

// 更新容器内对象提示
php artisan ide-helper:meta

// 更新模型类提示，需要连接数据库
php artisan ide-helper:models -W -R
<<<<<<< HEAD
```
=======
```
>>>>>>> 34e551813e5956c17c13abe83256dcecefc3a169
