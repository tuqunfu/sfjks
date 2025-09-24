layui.use(['layer'], function(){
  var layer = layui.layer;
  // 获取会员数据
  var memberData = [];
  $.ajax({
    url: "/index/user/get_membership_plan_list",
    type: "get",
    cache: false,
    success: function (res) {
      if(res){
        memberData = res;
      }
    }
  });

  // 点击选择会员
  document.getElementById('chooseMember').onclick = function(){
    var cardItems = '';
    memberData.forEach(function(member) {
      cardItems += `
        <form name=alipayment action="/index/user/user_membership" method="post" target="_blank">
          <input type="hidden"  name="id" value="${member.id}" />
          <div class="card-item" data-id="${member.id}" data-name="${member.name}" 
              data-type="${member.name}" data-price="${member.price}" data-time="${member.time}" data-avatar="${member.avatar}">
            <img style="width:80px;height:80px" class="card-avatar" src="/public/static/images/huiyuan.png" alt="${member.name}">
            <div class="card-type">${member.name}</div>
            <div class="card-price">￥${member.price}</div>
            <button style="padding: 0px 29px !important;" class="layui-btn pay-btn" type="submit">付    款</button>
          </div>
       </form>
      `;
    });

    layer.open({
      type: 1,
      title: '书法教科书',
      area: ['520px', '320px'],
      content: `
        <div class="card-grid">
          ${cardItems}
        </div>
      `,
      success: function(layero, index){
      }
    });
  };
});