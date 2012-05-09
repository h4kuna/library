(function($){$.imageTick={logging:false};$.fn.imageTick=function(options,disable){var defaults={tick_image_path:"",no_tick_image_path:"",image_tick_class:"ticks_"+Math.floor(Math.random()*999999),img_html:'<img src="%s1" alt="no_tick" class="%s2" id="tick_img_%s3" />',custom_button:false,custom_button_selected_class:'selected'};var opt=$.extend({},defaults,options);opt._tick_img_id_format='tick_img_%s';opt._valid_types=['checkbox','radio'];function log(){$.imageTick.logging&&console&&console.log&&console.log.apply(console,arguments);}
if(options==='disabled'){if(this.selector.indexOf('#')==-1){log('COULD NOT DISABLE "'+this.selector+'": You need to specify the id of the <input> when calling disabled true/false.');return;}
var $img_id=$('#'+opt._tick_img_id_format.replace('%s',this[0].id));if(disable){$(this).attr('disabled','disabled');method_type='add';}
else{$(this).removeAttr('disabled');method_type='remove';}
$img_id[method_type+'Class']('disabled');return;}
function imagePathsAreEqual(e){var current_img_src=e.src.split('/').pop();var no_tick_path=opt.no_tick_image_path.split('/').pop();return current_img_src==no_tick_path;}
function handleClickType(using_custom_button,type,$input_id){if(using_custom_button){if(type=='radio'){$("."+opt.image_tick_class).removeClass(opt.custom_button_selected_class);}
$(this).toggleClass(opt.custom_button_selected_class);}
else{if(type=='checkbox'){var img_src=(imagePathsAreEqual(this))?opt.tick_image_path:opt.no_tick_image_path;}
else{$("."+opt.image_tick_class).attr('src',opt.no_tick_image_path);var img_src=opt.tick_image_path;}
this.src=img_src;}}
this.each(function(){var $obj=$(this);var type=$obj[0].type;if($.inArray(type,opt._valid_types)==-1){return;}
var id=$obj[0].id;var $input_id=$('#'+id);var $label=$("label[for='"+id+"']");var img_id_format=opt._tick_img_id_format.replace('%s',id);var using_custom_btn=$.isFunction(opt.custom_button);var img_html='';if(using_custom_btn){img_html=$(opt.custom_button($label)).attr('id',img_id_format.replace('%s',id)).addClass(opt.image_tick_class);}
else{img_html=opt.img_html.replace('%s1',opt.no_tick_image_path).replace('%s2',opt.image_tick_class).replace('%s3',id);}
$obj.before(img_html).hide();var $img_id=$('#'+img_id_format);if($input_id[0].disabled){$img_id.addClass('disabled');}
if($obj[0].checked){if($img_id[0].src){$img_id[0].src=opt.tick_image_path;}
else{$img_id.addClass(opt.custom_button_selected_class);}}
$img_id.click(function(e){if($input_id[0].disabled){return;}
$input_id.trigger("click");handleClickType.call(this,using_custom_btn,type,$input_id);});if($label.length){$label.click(function(e){e.preventDefault();$img_id.trigger('click');});}});};})(jQuery);