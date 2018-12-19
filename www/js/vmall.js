$(function () {
    $(".nav-tabs-custom .nav-tabs").on('click','li',function () {
        //console.log($(this).text());
        var self = $(this);
        if (self.hasClass('active')) return false;
        self.siblings().removeClass('active');
        self.addClass('active');

        var url = self.find('a').attr('href');
        if(!url) return false;
        $('.-box-content').html('');
        $('#refresh').show();
        $.post(url, function (data) {
            $('#refresh').hide();
            $('.-box-content').html(data);
            return false;
        });
        //console.log(url);
        return false;
    });

    $(".nav-tabs-custom .nav-tabs li:first").click();

    // 菜单显示
    if(_ctrl && _act){
        $('#'+_ctrl).addClass('active');
        $('#'+_act).addClass('active');
        $('#'+_act).parent().addClass('menu-open');
    }

    // 分页
    $(".-box-content").on('click', '.pagination a', function () {
        var self = $(this),
            req_uri = self.parents('.pagination').attr('req_uri'),
            href = self.attr('href');
        if(!req_uri || !href){
            alert('请求参数获取失败！');return false;
        }
        $('.-box-content').html('');
        $('#refresh').show();
        var url = req_uri+''+href;
        $.post(url, function (data) {
            $('#refresh').hide();
            $('.-box-content').html(data);
            return false;
        });
        return false;
    });
});