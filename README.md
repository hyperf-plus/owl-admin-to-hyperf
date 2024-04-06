# owl-admin 迁移助手
## 介绍
owl-admin 迁移助手，目前仅支持owl-admin迁移到hyperf，后续会支持双向迁移。

owl-admin相当强大，但是基于laravel，而hyperf是基于swoole的，两者的底层架构不同，所以迁移是有一定难度的，这个工具就是为了解决这个问题而生的，
为什么要用hyperf来做这个后台？后台不需要高性能啊，laravel完全够用了，这个问题我也想过，但是我还是选择了hyperf，原因如下：
1. hyperf是基于swoole的，性能比laravel要好很多，而且swoole是未来的趋势，所以我选择了hyperf。
2. api接口基本都是基于hyperf，需要重新再维护一套后台，不如直接用hyperf来做。
3. 模型、service等等只需要创建一次、极大的减少了重复劳动。
4. 代码风格统一，维护方便。
5. hyperf深度使用者，可以更好的发挥hyperf的优势，开发效率远高于laravel。