<div :controller="{$EnName}List">
    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
        <div class="am-form-group">
            <div class="am-btn-toolbar">
                <div class="am-btn-group am-btn-group-xs">
                    <button type="button" class="am-btn am-btn-default am-btn-success" ms-click="goto('#!/{$EnName}/Add/0')"><span class="am-icon-plus"></span> 新增</button>
                    <!--<button type="button" class="am-btn am-btn-default am-btn-secondary"><span class="am-icon-save"></span> 保存</button>-->
                    <!--<button type="button" class="am-btn am-btn-default am-btn-warning"><span class="am-icon-archive"></span> 审核</button>-->
                    <!--<button type="button" class="am-btn am-btn-default am-btn-danger"><span class="am-icon-trash-o"></span> 删除</button>-->
                </div>
            </div>
        </div>
    </div>
    <div class="widget-body  widget-body-lg am-fr">
        <table width="100%" class="am-table am-table-compact tpl-table-black " id="example-r">
            <thead>
            <tr>
                <th>#</th>
                <th>字段名称</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            <tr class="gradeX" ms-for="(k,el) in @search.L">
                <td>{{k+1}}</td>
                <td>字段值</td>
                <td>
                    <div class="tpl-table-black-operation">
                        <a href="javascript:;" ms-click="goto('#!/Area/Edit/'+el.{$Object}ID)">
                            <i class="am-icon-pencil"></i> 编辑
                        </a>
                        <a href="javascript:;" class="tpl-table-black-operation-del" ms-click="del(el.{$Object}ID)">
                            <i class="am-icon-trash"></i> 删除
                        </a>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>

    </div>
</div>