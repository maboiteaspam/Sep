<?
$selected = is_string($selected)?array($selected):$selected;
foreach($options as $option=>$value){
    $options[$option] = array(
        "value"=>isset($value["value"])?$value["value"]:$value,
        "text"=>isset($value["text"])?$value["text"]:$value,
        "selected"=>false,
    );
    $options[$option]["selected"] = in_array($options[$option]["value"],$selected);
}
$max_list_length = 10;
$is_long = count($options)>$max_list_length;
?>
<div class="select_area"
     id="<?= $id ?>">
    <? if($label){ ?><label><?= $label; ?></label><? } ?>

    <div style="display: inline-block;">
        <input type="text"
               style="display: block;width: 100%;"
               class="checkbox_filter"
               placeholder="Filter options"
               data-bind="css:{hide:!is_long()}, value:search, valueUpdate:'afterkeydown'"
            />
        <table width="600">
            <tr>
                <td width="50%" style="padding-bottom:0;padding-left:0;">
                    <span data-bind="css:{hide:!has_more_items_available()}">
                    ...<span data-bind="text:available_options().length-max_length_list()"></span> more available
                    </span>
                    <span data-bind="css:{hide:has_more_items_available()}">
                    <span data-bind="text:visible_filtered_available_options().length"></span> item(s)</span>
                    <div style="height:100%;max-height:150px;overflow-y:auto;"
                          data-bind="foreach: visible_filtered_available_options">
                        <label data-bind="attr:{for:'<?= $id ?>_optiona'+$index()}"
                            >
                            <input type="checkbox"
                                   name="<?= $name; ?>[]"
                                   data-bind="attr:{id:'<?= $id ?>_optiona'+$index()},
                           checkedValue: value,
                           checked: $parent.selected_values
                                <?= $read_only?",enable:false":""; ?>
                           "
                                />
                            <span data-bind="text:text()"></span>
                        </label>
                    </div>
            </td>
                <td style="padding-bottom:0;padding-left:0;">
            <span data-bind="if:selected_options().length>0">
            selected <span data-bind="text:selected_options().length"></span> item(s)
            </span>
            <span data-bind="if:required_length()>0&&selected_options().length==0">
            select at least <span data-bind="text:required_length()"></span> item(s)</span>
                    <div style="height:100%;max-height:150px;overflow-y:auto;"
                         data-bind="foreach: selected_options">
                        <label data-bind="attr:{for:'<?= $id ?>_optionb'+$index()}"
                            >
                            <input type="checkbox"
                                   name="<?= $name; ?>[]"
                                   data-bind="attr:{id:'<?= $id ?>_optionb'+$index()},
                           checkedValue: value,
                           checked: $parent.selected_values
                                <?= $read_only?",enable:false":""; ?>
                           "
                                />
                            <span data-bind="text:text()"></span>
                        </label>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
<script>
    (function(area){

        var stringStartsWith = function(a,b){
            return a.toLowerCase().substring(0, b.length)==b.toLowerCase();
        };
        var stringContains = function(a,b){
            return a.toLowerCase().indexOf(b.toLowerCase())>-1;
        };
        var stringSomehowMatch = function(a,b){
            return stringStartsWith(a,b) || stringContains(a,b);
        };
        var OptionsModel = function(options,max_length_list){
            var that = this;
            that.options = ko.observableArray();
            that.required_length = ko.observable();
            that.selected_values = ko.observableArray();
            for(var n in options){
                that.options.push({
                    value:ko.observable(options[n].value),
                    text:ko.observable(options[n].text)
                });
                if( options[n].selected ){
                    that.selected_values.push(options[n].value);
                }
            }
            that.search = ko.observable("");
            that.max_length_list = ko.observable(10);
            this.is_long = ko.computed(function(){
                return that.options().length > that.max_length_list();
            }, that).extend({ throttle: 50 });

            that.available_options = ko.computed(function() {
                var options = that.options();
                return ko.utils.arrayFilter(options, function(opt) {
                    return that.selected_values().indexOf(opt.value()) == -1;
                });
            }, that).extend({ throttle: 50 });
            this.has_more_items_available = ko.computed(function(){
                return that.is_long()&&
                    that.available_options().length-that.max_length_list()>0
            }, that).extend({ throttle: 50 });
            that.selected_options = ko.computed(function() {
                var options = that.options();
                return ko.utils.arrayFilter(options, function(opt) {
                    return that.selected_values().indexOf(opt.value()) > -1;
                });
            }, that).extend({ throttle: 50 });
            that.filtered_options = ko.computed(function(){
                var options = that.options();
                if (that.search().length > 0) {
                    return ko.utils.arrayFilter(options, function(opt) {
                        return (stringSomehowMatch(opt.text(), that.search()) ||
                            stringSomehowMatch(opt.value(), that.search()));
                    });
                }
                return options;
            }, that).extend({ throttle: 50 });
            that.filtered_available_options = ko.computed(function() {
                var options = that.filtered_options();
                return ko.utils.arrayFilter(options, function(opt) {
                    return that.selected_values().indexOf(opt.value()) == -1;
                });
            }, that).extend({ throttle: 50 });
            that.visible_filtered_available_options = ko.computed(function() {
                var options = that.filtered_available_options();
                return options.slice(0,that.max_length_list());
            }, that).extend({ throttle: 50 });
            that.filtered_selected_options = ko.computed(function() {
                var options = that.filtered_options();
                return ko.utils.arrayFilter(options, function(opt) {
                    return that.selected_values().indexOf(opt.value()) > -1;
                });
            }, that).extend({ throttle: 50 });
            that.visible_filtered_selected_options = ko.computed(function() {
                var options = that.filtered_selected_options();
                return options.slice(0,that.max_length_list());
            }, that).extend({ throttle: 50 });
        };

        var options = <?= json_encode($options); ?>;
        var max_length_list = <?= json_encode($max_length_list); ?>;
        var optionsView = new OptionsModel(options,max_length_list);
        optionsView.required_length(<?= json_encode($required?1:0); ?>)
        ko.applyBindings(optionsView, area.get(0))

    })($("#<?= $id ?>.select_area"));
</script>