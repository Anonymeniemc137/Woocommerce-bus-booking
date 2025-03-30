/**
 * Custom scripts for making the admin more user friendly. 
 */
var mtrap_stop_select_container;
var mtrap_prices_container;

jQuery(function(){
  jQuery("#mtrap_bus_stops").change(function() {
    let station_id = [];
    jQuery('input[name^="station_id[]"]').each(function(){
        if( jQuery(this).val() ){
          station_id.push(jQuery(this).val());
        }
    });
    let mtrap_selected_stop = jQuery("#mtrap_bus_stops").find(":selected").val();
    jQuery.ajax({
      type: "post",
      url: ajaxfilter.ajax_url,
      data : {
        selected_stop : mtrap_selected_stop,
        action : "mtrap_callback_function_selected_stop",
        security : ajaxfilter.ajax_nonce,
        delete_station: station_id
      },  
      beforeSend: function() {
        jQuery('.mtrap-boarding-loader').show();
      },
      success: function(data) {
        jQuery('.mtrap-boarding-loader').hide();
        jQuery(".bus-details").html(data);
        
        // Add time picker
        jQuery('.boarding-time').timepicker({ timeFormat: 'H:mm:ss', interval: 15 });
        jQuery('.station_time').timepicker({ timeFormat: 'H:mm:ss' , interval: 15 });
        jQuery('.station_departure_time').timepicker({ timeFormat: 'H:mm:ss' , interval: 15 });
      },
      error: function(data) {
        console.log(data);
        jQuery('.mtrap-boarding-loader').hide();
        alert('Something went wrong!');
      },
      complete: function() { 
        setTimeout(
          function() 
          {
            window.mtrap_stop_select_container = jQuery(".mtrap_bus_stops_callback").parent('div').html();
            window.mtrap_prices_container = jQuery(".passenger-type").html();
          },1000);
       }
    });
    return false;
  });
});



