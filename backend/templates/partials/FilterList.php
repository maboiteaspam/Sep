<div class="filters_list">
    <form class="filter_select">
        <select name="view"
                data-bind="css: {hide:views().length==0},
                        options: select_views,
                        optionsText: 'text',
                        optionsValue: 'value',
                        selectedOptions: chosen_view">
        </select>
        <a href="#"
           class="btn_new link_area"
           name="new_view"
           data-bind="click:showNewFilter,text:editFilterText"
            ><?= $new_filter_label ?></a>
        <a href="#" data-bind="attr:{href:filterUrl},css:{hide:views().length==0}"
           class="link_area"
           name="browse_view"
            ><?= $visit_filter_label ?></a>
    </form>
    <div class="filter_add hide">
        <form method="POST" action="">
            <input type="hidden" name="filter_type" />
            <?= $view_name_label ?> <input type="text" data-bind="value: name" />
            <div class="filter_columns">
                <select multiple="" size="5"
                        style="width: 250px" name="available_headers"
                        data-bind="options: available_headers,
                        optionsText: 'text',
                        optionsValue: 'value'">
                </select>
                <select multiple="" size="5"
                        style="width: 250px" name="columns"
                        data-bind="options: new_item.selected_headers,
                        optionsText: 'text',
                        optionsValue: 'value'">
                </select>
            </div>
            <div class="filter_conditions">
                <table class="pure-table-horizontal">
                    <tbody data-bind="foreach: new_item.conditions">
                        <tr class="filter_condition">
                            <td class="left_field" data-bind="text: left_field"></td>
                            <td class="operator" data-bind="text: operator"></td>
                            <td class="right_field">
                                <span data-bind="css:{hide:operator=='IS NULL'||operator=='IS NOT NULL'},text: right_field"></span>
                            </td>
                            <td class="edit_btn">
                                <input type="submit"
                                       data-bind="click: $parent.new_item.editCondition"
                                       value="<?= $edit_condition_label ?>" />
                            </td>
                            <td class="del_btn">
                                <input type="submit"
                                       data-bind="click:$parent.new_item.delCondition"
                                       value="<?= $remove_condition_label ?>" />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="add_filter_condition">
                    <select name="left_field_type"
                        data-bind="options: new_item.left_field_type,
                                    selectedOptions: new_item.chosen_left_field_type">
                    </select>
                    <select name="left_field"
                        data-bind="css:{hide:new_item.is_text_left()},
                            options: new_item.left_filterable,
                            optionsText: 'text',
                            optionsValue: 'value',
                            selectedOptions: new_item.left_field_value">
                    </select>
                    <input name="left_field"
                           data-bind="css:{hide:!new_item.is_text_left()},
                                        value:new_item.left_field_value">
                    <select name="operator"
                        data-bind="options: new_item.operators,
                            selectedOptions: new_item.chosen_operator">
                    </select>
                    <select name="right_field_type"
                        data-bind="attr:{disabled:!new_item.is_right_visible()},
                            options: new_item.right_field_type,
                            selectedOptions: new_item.chosen_right_field_type">
                    </select>
                    <select name="right_field"
                        data-bind="
                            attr:{disabled: !new_item.is_right_visible()},
                            css:{hide:new_item.is_text_right()},
                            options: new_item.right_filterable,
                            optionsText: 'text',
                            optionsValue: 'value',
                            selectedOptions: new_item.right_field_value">
                    </select>
                    <input name="right_field"
                           data-bind="css:{hide:!new_item.is_text_right()},
                           attr:{disabled:!new_item.is_right_visible()},
                                        value:new_item.right_field_value">
                    <input type="submit" data-bind="click:addCondition"
                           value="<?= $add_condition_label ?>" />
                </div>
            </div>
            <input type="submit" value="<?= $save_filter_label ?>"
                   data-bind="click:save" />
            <input type="submit" value="<?= $reset_filter_label ?>"
                   data-bind="click:reset" />
            <input type="submit" value="<?= $delete_filter_label ?>"
                   data-bind="click:delete_view,attr:{disabled:chosen_view()==''}"  />
            <ul data-bind="foreach: errors">
                <li data-bind="text: $data"></li>
            </ul>
        </form>
    </div>
</div>
<script>
    var myViewModel = {};

    myViewModel = {
        views: ko.observableArray(<?= json_encode($views) ?>),
        conditions_views: ko.observableArray(<?= json_encode($conditions_views) ?>),
        filter_type: (<?= json_encode($filter_type) ?>),
        chosen_view: ko.observable(''),
        operators:ko.observableArray(<?= json_encode($operators) ?>),
        id:ko.observable(null),
        name:ko.observable(''),
        new_item:{},
        headers: ko.observableArray(<?= json_encode($headers) ?>),
        errors:ko.observableArray([]),
        available_filterable: ko.observableArray(<?= json_encode($filterable) ?>)
    };

    myViewModel.filterUrl = ko.computed(function(){
        var filter_id = myViewModel.chosen_view()[0];
        var u = window.location.origin;
        u += window.location.pathname;
        if( filter_id ){
            u += "?filter_id="+filter_id;
        }
        return u;
    },myViewModel);
    myViewModel.select_views = ko.computed(function(){
        var v = this.views();
        var r = [{text:"none",value:''}];
        for( var n in v ){
            if(v[n].filter_type == myViewModel.filter_type ){
                r.push({text:v[n].name,value:v[n].id});
            }
        }
        return r;
    },myViewModel);
    myViewModel.editFilterText = ko.computed(function(){
        var v = this.chosen_view();
        if( v == "" ){
            return "<?= $new_filter_label ?>";
        }
        return "<?= $edit_filter_label ?>";
    },myViewModel);
    myViewModel.field_types = ko.computed(function(){
        var r = [
            'property',
            'value',
            'rawsql',
        ];
        if( this.views().length > 0 ){
            r.push("view");
        }
        return r;
    },myViewModel);
    myViewModel.new_item.name = ko.observable('');
    myViewModel.new_item.selected_headers = ko.observableArray([]);
    myViewModel.new_item.conditions = ko.observableArray([]);
    myViewModel.new_item.left_field_value = ko.observable('');
    myViewModel.new_item.right_field_value = ko.observable('');
    myViewModel.new_item.chosen_operator = ko.observable('');
    myViewModel.new_item.chosen_left_field_type = ko.observable('');
    myViewModel.new_item.chosen_right_field_type = ko.observable('');
    myViewModel.new_item.chosen_operator = ko.observable('');
    myViewModel.new_item.left_filterable = ko.computed(function(){
        return this.available_filterable();
    },myViewModel);
    myViewModel.new_item.right_filterable = ko.computed(function(){
        if( this.new_item.chosen_right_field_type()[0] == "view"){

            var c_id = myViewModel.chosen_view()[0];
            var filter_type = null;
            var left_type = myViewModel.new_item.chosen_left_field_type();
            var left_value = myViewModel.new_item.left_field_value();
            if( left_type == "property" && left_value[0].match(/[^.]+[.][^.]+/i)){
                filter_type = left_value[0].match(/([^.]+)[.][^.]+/i)[1];
            }
            var r = [];
            var v = myViewModel.conditions_views();
            for( var n in v ){
                var view = v[n];
                console.log((filter_type !== null && view.filter_type == filter_type))
                if( view.id !== c_id &&
                    (view.filter_type == filter_type || filter_type===null)){
                    r.push({text:view.name,value:view.id});
                }
            }
            return r;
        }
        return this.available_filterable();
    },myViewModel);
    myViewModel.new_item.left_field_type = ko.computed(function(){
        return [
            'property',
            'value',
            'rawsql',
        ];
    },myViewModel);
    myViewModel.new_item.right_field_type = ko.computed(function(){
        var op = myViewModel.new_item.chosen_operator();
        if( op && op.indexOf("IN")>-1 || op.indexOf("NOT IN")>-1 ){
            if( myViewModel.conditions_views().length>0){
                return [
                    'value',
                    'rawsql',
                    'view',
                ];
            }else{
                return [
                    'value',
                    'rawsql',
                ];
            }
        }
        return [
            'property',
            'value',
            'rawsql',
        ];
    },myViewModel);
    myViewModel.new_item.operators = ko.computed(function(){
        return this.operators();
    },myViewModel);
    myViewModel.new_item.is_text_left = ko.computed(function(){
        var a = ['value','rawsql'];
        var b = this.new_item.chosen_left_field_type()[0];
        return a.indexOf(b)>-1;
    },myViewModel);
    myViewModel.new_item.is_view_right = ko.computed(function(){
        var b = this.new_item.chosen_right_field_type()[0];
        return ['view'].indexOf(b)>-1;
    },myViewModel);
    myViewModel.new_item.is_text_right = ko.computed(function(){
        var a = ['value','rawsql'];
        var b = this.new_item.chosen_right_field_type()[0];
        return a.indexOf(b)>-1;
    },myViewModel);
    myViewModel.new_item.is_right_visible = ko.computed(function(){
        var a = ['IS NULL','IS NOT NULL'];
        var b = this.new_item.chosen_operator()[0];
        return a.indexOf(b)==-1;
    },myViewModel);
    myViewModel.new_item.editCondition =function(){
        myViewModel.new_item.chosen_operator([this.operator]);
        myViewModel.new_item.left_field_value([this.left_field]);
        myViewModel.new_item.right_field_value([this.right_field]);
        myViewModel.new_item.chosen_right_field_type([this.right_field_type]);
        myViewModel.new_item.chosen_left_field_type([this.left_field_type]);
        myViewModel.new_item.conditions.remove(this);
    };
    myViewModel.new_item.delCondition =function(){
        myViewModel.new_item.conditions.remove(this);
    };
    myViewModel.available_headers = ko.computed(function(){
        return ko.utils.arrayFilter(myViewModel.headers(), function(h) {
            return ko.utils.arrayFilter(myViewModel.new_item.selected_headers(), function(sh) {
                return sh.value==h.value;
            }).length==0;
        });
    },myViewModel);
    myViewModel.showNewFilter = function(){
        if( $(".filter_add").hasClass("hide") ){
            $(".filter_add").removeClass("hide");
        }else{
            $(".filter_add").addClass("hide");
        }
    };
    myViewModel.new_item.reset = function(){
        myViewModel.new_item.chosen_operator([myViewModel.operators()[0]]);
        myViewModel.new_item.left_field_value([myViewModel.new_item.left_filterable()[0].value]);
        myViewModel.new_item.right_field_value([myViewModel.new_item.right_filterable()[0].value]);
        myViewModel.new_item.chosen_right_field_type([myViewModel.new_item.right_field_type()[0]]);
        myViewModel.new_item.chosen_left_field_type([myViewModel.new_item.left_field_type()[0]]);
    };
    myViewModel.addCondition = function(){
        var left_field_value = myViewModel.new_item.left_field_value();
        left_field_value = $.isArray(left_field_value)?left_field_value[0]:left_field_value;
        var right_field_value = myViewModel.new_item.right_field_value();
        right_field_value = $.isArray(right_field_value)?right_field_value[0]:right_field_value;
        myViewModel.new_item.conditions.push(
            {
                'id':'',
                'left_field_type':myViewModel.new_item.chosen_left_field_type()[0],
                'left_field':left_field_value,
                'operator':myViewModel.new_item.chosen_operator()[0],
                'right_field_type':myViewModel.new_item.chosen_right_field_type()[0],
                'right_field':right_field_value
            }
        );
        myViewModel.new_item.reset();
    };
    myViewModel.save = function(){
        myViewModel.errors.removeAll();
        if( myViewModel.name() == "" ){
            myViewModel.errors.push("<?= $missing_name_error; ?>");
        }
        if( myViewModel.new_item.selected_headers().length == 0 ){
            myViewModel.errors.push("<?= $missing_columns_error; ?>");
        }
        var data = {
            name:myViewModel.name(),
            filter_type:myViewModel.filter_type,
            conditions:[],
            columns:[]
        };
        var c_items = myViewModel.new_item.conditions();
        for(var n in c_items ){
            data.conditions.push({
                'id':'',
                'left_field_type':c_items[n].left_field_type,
                'left_field':c_items[n].left_field,
                'operator':c_items[n].operator,
                'right_field_type':c_items[n].right_field_type,
                'right_field':c_items[n].right_field
            });
        }
        c_items = myViewModel.new_item.selected_headers();
        for(var n in c_items ){
            data.columns.push(c_items[n].value);
        }
        if( myViewModel.errors().length == 0){
            var post_url = "/Filter";
            if( myViewModel.id() ){
                post_url += "/"+myViewModel.id();
            }
            $.post(post_url,data,function(res){
                var s = jQuery.parseJSON(res);
                if(myViewModel.id()){
                    myViewModel.views.remove(function(item) { return item.id == myViewModel.id() })
                }
                myViewModel.views.push(
                    s
                );
                myViewModel.chosen_view([s.id]);
                myViewModel.showNewFilter();
            });
            myViewModel.new_item.reset();
        }
    };
    myViewModel.delete_view = function(){
        var id = myViewModel.chosen_view()[0];
        $.post("/Filter/delete/"+id,{},function(res){
            var views = myViewModel.views();
            for(var n in views ){
                if( views[n].id == id ){
                    myViewModel.chosen_view([]);
                    myViewModel.views.remove(views[n]);
                    break;
                }
            }
        });
    };

    function apply_view(view){
        myViewModel.id(view.id);
        myViewModel.name(view.name);
        myViewModel.new_item.conditions([]);
        for( var n in view.conditions ){
            myViewModel.new_item.conditions.push(
                {
                    'id':'',
                    'left_field_type':view.conditions[n].left_field_type,
                    'left_field':view.conditions[n].left_field,
                    'operator':view.conditions[n].operator,
                    'right_field_type':view.conditions[n].right_field_type,
                    'right_field':view.conditions[n].right_field
                }
            );
        }
        myViewModel.new_item.selected_headers([]);
        for( var n in view.columns ){
            myViewModel.new_item.selected_headers.push(
                ko.utils.arrayFilter(myViewModel.headers(), function(h) {
                return h.value==view.columns[n];
            })[0]);
        }
    }

    myViewModel.setEmptyView = function(){
        myViewModel.new_item.reset();
        myViewModel.id(null);
        myViewModel.name("");
        myViewModel.new_item.selected_headers([]);
        myViewModel.new_item.conditions([]);
    };
    myViewModel.reset = function(){
        var id = myViewModel.id();
        var f = ko.utils.arrayFilter(myViewModel.views(), function(item) {
            return item.id === id;
        });
        if(f.length > 0 ){
            f = f[0];
            apply_view(f);
        }else{
            myViewModel.setEmptyView();
        }
    };
    myViewModel.chosen_view.subscribe(function(k){
        var f = ko.utils.arrayFilter(myViewModel.views(), function(item) {
            return item.id == k;
        });
        if(f.length > 0 ){
            f = f[0];
            apply_view(f);
        }else{
            myViewModel.setEmptyView();
        }
    });
    myViewModel.new_item.reset();
    myViewModel.chosen_view(['<?= $filter_id; ?>']);

    ko.applyBindings(myViewModel,$(".filters_list").get(0));
    $("select[name='available_headers']").on("dblclick","option",function(){
        var that = this;
        myViewModel.new_item.selected_headers.push( ko.utils.arrayFilter(myViewModel.headers(), function(h) {
            return h.value == $(that).val();
        })[0] );
    });
    $("select[name='columns']").on("dblclick","option",function(){
        var that = this;
        myViewModel.new_item.selected_headers.remove( ko.utils.arrayFilter(myViewModel.headers(), function(h) {
            return h.value == $(that).val();
        })[0] );
    });
</script>