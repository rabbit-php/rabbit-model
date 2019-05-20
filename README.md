校验模型
========

说明
----
校验采用第三方包`respect/validation`

具体用法
-----
1. 继承`rabbit\model\Model`类，编写`rules`规则
2. 直接调用`ValidateHelper::validate`方法校验