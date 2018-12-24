$(function () {
    $(".nav-tabs-custom .nav-tabs").on('click','li',function () {
        //console.log($(this).text());
        var self = $(this), search_class = self.attr('search-class'),
            finish_status = self.parent().prop('finish-status');
        if (finish_status==false || self.hasClass('active')) return false;
        self.parent().prop('finish-status', false);
        self.siblings().removeClass('active');
        self.addClass('active');
        $(".tabs-modify").hide();
        $('.search-body').hide();
        if(search_class){
            $('.'+search_class).show();
        }

        var url = self.find('a').attr('href');
        if(!url) return false;
        $('.-box-content').html('');
        $('#refresh').show();
        $.post(url, function (data) {
            $('.-box-content').html(data);
            $('#refresh').hide();
            self.parent().prop('finish-status', true);
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
            href = self.attr('href'), postData = $("#search-form").serialize();
        if(!req_uri || !href){
            alert('请求参数获取失败！');return false;
        }
        $('.-box-content').html('');
        $('#refresh').show();
        var url = req_uri+''+href;
        $.post(url, postData, function (data) {
            $('.-box-content').html(data);
            $('#refresh').hide();
            return false;
        });
        return false;
    });

    // icheck点击操作
    $(".-box-content").on('click','.icheck-ajax',function () {
        var self = $(this), url = self.attr('url'),pid = self.attr('pid'),
            icheck = self.find('.icheckbox_flat-green'),
            type = icheck.attr('aria-checked');
        if(!confirm('确定进行此操作')) return false;
        if(!type || !url || !pid){
            alert('参数错误');return false;
        }
        $.post(url,{pid:pid,type:type},function (data) {
            if (data.code==200){
                if (type == 1) {
                    icheck.removeClass('checked');
                    icheck.attr('aria-checked', 2);
                }else{
                    icheck.addClass('checked');
                    icheck.attr('aria-checked', 1);
                }
            } else{
                alert(data.msg);
            }
        });

        return false;
    });

    // btn删除
    $(".-box-content").on('click','.btn_del',function () {
        var self = $(this),url = self.attr('url'),text = self.text(),
        tr = $(this).parent().parent().parent();
        if(!url) return false;
        if(text && !confirm('确认执行'+text)) return false;
        $.post(url,function(data){
            if (data.code == 200) {
                tr.remove();
            }else{
                alert(data.msg);
            }
        });
    });
});