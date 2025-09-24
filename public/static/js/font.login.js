function login(layer,form){
    layer.open({
      type: 1,
      title: '书法教科书',
      area: ['400px', '420px'],
      content: $('#loginTpl').html(),
      success: function(layero, index){
        // 重新渲染表单
        form.render();
        // 注册切换事件
        $('.switch-to-register').on('click', function(){
          layer.close(index);
          register(layer,form);
        });
        // 登录提交
        form.on('submit(login)', function(data){
         $.ajax({
            url: "/index/user/login",
            type: "post",
            dataType: "json",
            cache: false,
            data: {
              password: data.field.password,
              username: data.field.username
            },
            success: function (res) {
              if(res.code==1){
                layer.msg('登录成功');
                location.reload();
              }else{
                layer.msg(res.msg);
              }
              // layer.close(index);
              // return false;
            }
          });
          // return false;
        });
      }
    });
  }

  function register(layer,form){
    layer.open({
      type: 1,
      title: '书法教科书',
      area: ['400px', '550px'],
      content: $('#registerTpl').html(),
      success: function(layero, index){
        form.render();
        // 切换到登录
        $('.switch-to-login').on('click', function(){
          layer.close(index);
          login(layer,form);
        });
        // 注册提交（可添加密码一致性验证）
        form.on('submit(register)', function(data){
          if (data.field.reg_password !== data.field.confirm) {
            layer.msg('两次密码不一致！');
            return false;
          }
          $.ajax({
            url: "/index/user/register",
            type: "post",
            dataType: "json",
            cache: false,
            data: {
              password: data.field.reg_password,
              user_name: data.field.reg_username,
              mobile: data.field.mobile
            },
            success: function (data) {
              if(data.code==1){
                layer.msg('注册成功！');
                location.reload();
              }else{
                layer.msg(data.msg);
              }
              layer.close(index);
              return false;
            }
          });

        });
      }
    });
  }
  
  function getuser(){
    $.ajax({
        url: "/index/user/get_user_info",
        type: "get",
        success: function (info) {
          if(info!=null){
            $(".nlogin").css("display","none");
            $(".inlogin").css("display","block");
            $(".user").html(info.user_name);
            if(info.membership!=null){
              $("#chooseMember").css("display","none");
              $("#userMember").css("display","block");
              $("#membership").html(info.membership);
            }else{
              $("#chooseMember").css("display","block");
              $("#userMember").css("display","none");
              $("#membership").html('');
            }
          }else{
            $(".nlogin").css("display","block");
            $(".inlogin").css("display","none");
          }
        }
    });
  }

  layui.use(['layer', 'form', 'jquery'], function(){
    var layer = layui.layer;
    var form = layui.form;
    var $ = layui.jquery;
    // 打开登录弹窗
    $('#showLogin').on('click', function(){
      login(layer,form);
    });
    // 打开注册弹窗
    $('#showRegister').on('click', function(){
      register(layer,form);
    });

    $("#loginOut").on('click',function(){
      $.ajax({
        url: "/index/user/logout",
        type: "post",
        dataType: "json",
        cache: false,
        data: {},
        success: function (data) {
          if(data.code==1){
            layer.msg('注销成功！');
            location.reload();
          }else{
            layer.msg(data.msg);
          }
          return false;
        }
      })
    });

  });