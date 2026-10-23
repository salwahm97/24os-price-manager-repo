jQuery(document).ready(function () {

    var menu_id = jQuery(".menu_id").attr('id');
    
    save_visitor_info(menu_id);
});
        
function save_visitor_info(menu_id) {
    var data = {
        action: "menupricemanagervisitorlibrary",
        param: "save_visitor_info",
        menu_id:menu_id 
    }
    jQuery.post(menupricemanagervisitorajaxurl, data, function(response)
    {
        var result = jQuery.parseJSON(response);
        if(result.status == 1)
        {
            console.log(result.message);
        }
        else{
            console.log(result.message);
        }
    
    });
}