/**
 * Custom scripts for making the admin more user friendly. 
 */

jQuery(function ($) {
  let x; 
  
  // Add timepicker funtionality for backend.
  $('.boarding-time').timepicker({ timeFormat: 'H:mm:ss' , interval: 15  });
  $('.station_time').timepicker({ timeFormat: 'H:mm:ss' , interval: 15  });
  $('.station_departure_time').timepicker({ timeFormat: 'H:mm:ss' , interval: 15 });
  
  // daterange picker script for date settings of the bus.
  if ($("body").hasClass("post-type-product")) {
    $('input[name="off-dates-range"]').daterangepicker({
      "linkedCalendars": false,
      "autoUpdateInput": false,
      "showCustomRangeLabel": false,
      locale: {
          cancelLabel: 'Clear'
      }
    }, function (start, end) {
      $('#off-dates-range').val(start.format('DD-MM-YYYY') + ' to ' + end.format('DD-MM-YYYY'));
      // console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
    });
    
    $('#off-dates-range').on('cancel.daterangepicker', function(ev, picker) {
      $('#off-dates-range').val('');
  });

    // Clone elements and enhance other stations.
    $(document).on("click", ".mtrap-add-bus-stop", function () {
      x = 1; //Initial field counter is 1
      let maxField = 100; //Input fields increment limitation

      if (mtrap_stop_select_container !== undefined) {
        var bus_stop = mtrap_stop_select_container;
      } else {
        var bus_stop = jQuery(".mtrap_bus_stops_callback").parent('div').clone().find(':selected').removeAttr('selected').end().html();
      }
      
      if (mtrap_prices_container !== undefined) {
        var bus_prices = mtrap_prices_container;
      } else {
        var bus_prices = jQuery(".passenger-type").html();
      }

      let fieldHTML = '<div class="field-section">'; //New input field html
      fieldHTML += '<div class="mtrap-admin-meta-heading">';
      fieldHTML += '<h4>' + mtrapbusstops.mtrap_label + '</h4>';
      fieldHTML += '</div>';
      fieldHTML += '<div>' + bus_stop + '</div>';
      fieldHTML += '<div class="passenger-type">' + bus_prices + '</div>';
      fieldHTML += '<div>';
      fieldHTML += '<label for="station_day">' + mtrapbusstops.mtrap_day + '</label>';
      fieldHTML += '<select name="station_day[]" class="mtrap_station_days_callback">';
      fieldHTML += '<option value="same-day">' + mtrapbusstops.mtrap_select_reach_same_day + '</option>';
      fieldHTML += '<option value="different-day">' + mtrapbusstops.mtrap_select_reach_differrent_day + '</option>';
      fieldHTML += '</select>';
      fieldHTML += '</div><div style="display:none">';
      fieldHTML += '<label for="station_day_difference">' + mtrapbusstops.mtrap_select_reach_day_difference + '</label>';
      fieldHTML += '<input type="number" class="station_day_difference" name="station_day_difference[]" min="0" max="10" value="">';
      fieldHTML += '</div><div>';
      fieldHTML += '<label for="station_time">' + mtrapbusstops.mtrap_stop_time + '</label>';
      fieldHTML += '<input type="text" class="station_time" name="station_time[]" value="0:00:00">';
      fieldHTML += '</div><div>';
      fieldHTML += '<label for="station_departure_time">' + mtrapbusstops.mtrap_stop_time_departure + '</label>';
      fieldHTML += '<input type="text" class="station_departure_time" name="station_departure_time[]" value="0:00:00">';
      fieldHTML += '<button type="button" class="mtrap-remove-bus-stop button button-primary button-large">' + mtrapbusstops.mtrap_remove_bus_stop + '</button>';
      fieldHTML += '';
      fieldHTML += '</div></div>';

      //Check maximum number of input fields
      if (x < maxField) {
        x++; //Increase field counter
        $('.mtrap-more-bus-stops').append(fieldHTML); //Add field html
        
        // Add time picker
        $('.boarding-time').timepicker({ timeFormat: 'H:mm:ss', interval: 15  });
        $('.station_time').timepicker({ timeFormat: 'H:mm:ss', interval: 15  });
        $('.station_departure_time').timepicker({ timeFormat: 'H:mm:ss' , interval: 15 });

      } else {
        alert('A maximum of ' + maxField + ' fields are allowed to be added. ');
      }
    });


    // Once remove button is clicked
    $(document).on('click', '.mtrap-remove-bus-stop', function (e) {
      e.preventDefault();
      let data_id = $(this).attr('data-id');
      let current = $(this);
      if( data_id !== undefined ){
        jQuery.ajax({
          type: "post",
          url: ajaxfilter.ajax_url,
          data : {
            stop_id : data_id,
            action : "mtrap_callback_remove_station",
            security : ajaxfilter.ajax_nonce_routing
          },  
          beforeSend: function() {
           $('.mtrap-remove-bus-stop').prop("disabled", true);
           $(".mtrap-boarding-loader").show();
          },
          success: function() {
            $('.mtrap-remove-bus-stop').prop("disabled", false);
            $('.mtrap-boarding-loader').hide();
            current.closest('.field-section').remove();
          },
          error: function(data) {
            console.log(data);
            $('.mtrap-remove-bus-stop').prop("disabled", false);
            $('.mtrap-boarding-loader').hide();
            alert('Something went wrong please check console!');
          },
        });
      }else{
        current.closest('.field-section').remove();
      }
      x--; //Decrease field counter
    });

    $(document).on('change', '.mtrap_bus_stops_callback', function (e) {
      e.preventDefault();
      let stop_id = $(this).val();
      let current_selection = $(this);
      let checker = {};
      
      // Check dublicate station.
      $(".mtrap_bus_stops_callback").each(function () {
        let selection = $(this).val();
        if (checker[selection]) {
          current_selection.val('');
          alert("This station is already added!");
          return false;
        } else {
          checker[selection] = true;
        }
      });

      // Append station ID.
      let pessenger_type = $(this).parent().next('.passenger-type').find('input');
      $(pessenger_type).each(function () {
        let term_slug = $(this).attr('term-slug');
        $(this).attr('name','passenger_type_pricing['+stop_id+']['+term_slug+'][]');
        console.log(this,'pass');
      });
      
    });

  }
  if ($("body").hasClass("taxonomy-bus-stops")) {
    $(".mtrap-tax-city-selection").select2({
      closeOnSelect: false,
      placeholder: "Choose other cities for creating the route",
      allowClear: true,
      tags: true
    });
  }
  
   $(document).on('change', '.mtrap_station_days_callback', function () {
    //e.preventDefault();
    let station_day = $(this).val();
    $(this).parent().next('div').find('.station_day_difference').val('');
    if (station_day == 'different-day') {
      $(this).parent().next('div').show();
    } else {
      $(this).parent().next('div').hide();
    }
  });

});


