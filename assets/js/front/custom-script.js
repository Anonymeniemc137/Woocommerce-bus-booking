
jQuery(document).ready(function () {
  // When the user scrolls the page, execute stickyHeader.
  window.onscroll = function () { stickyHeader() };
  window.mtrap_passenger_container = jQuery(".mtrap_passenger_type").parent('div').html();

  // Get the header
  let site_header = document.getElementById("my-sticky-header");

  // Get the offset position of the navbar.
  let header_sticky = site_header.offsetTop;

  // Add the sticky class to the header when you reach its scroll position. Remove "sticky" when you leave the scroll position.
  function stickyHeader() {
    if (window.pageYOffset > header_sticky) {
      site_header.classList.add("sticky");
    } else {
      site_header.classList.remove("sticky");
    }
  }

  // Birthdate datepicker.
  if (jQuery("body").hasClass("woocommerce-account")) {
    jQuery("#mtrap_user_birth_date").datepicker({
      maxDate: new Date(),
      dateFormat: 'dd-mm-yy',
      showOn: "both",
      buttonImage: ajaxObj.mtrap_calander_img,
      buttonImageOnly: true,
      changeMonth: true,
      changeYear: true, yearRange: '-100:'
    });
  }

  // Booking date datepicker.
  if (jQuery(".booking_form").length > 0) {
    jQuery(".bookingdate").datepicker({
      minDate: new Date(),
      dateFormat: 'dd-mm-yy',
      numberOfMonths: 1,
      onSelect: function (selected) {
        let selectedDt = selected.split(/\s*\-\s*/g);
        let DtFormat = selectedDt[1] + '/' + selectedDt[0] + '/' + selectedDt[2];
        let dt = new Date(DtFormat);
        dt.setDate(dt.getDate());
        jQuery(".returndate").datepicker("option", "minDate", dt);
        jQuery(".returndate").prop('disabled', false);
      }
    });
    

    // Return date datepicker.
    jQuery(".returndate").datepicker({
      numberOfMonths: 1,
      dateFormat: 'dd-mm-yy',
      onSelect: function (selected) {
        let selectedDt = selected.split(/\s*\-\s*/g);
        let DtFormat = selectedDt[1] + '/' + selectedDt[0] + '/' + selectedDt[2];
        let dt = new Date(DtFormat);
        dt.setDate(dt.getDate());
        jQuery(".bookingdate").datepicker("option", "maxDate", dt);
      }
    });
  }

  // on search if return date is not selected than convert it to one way trip.
  setTimeout( function() {
    var currentContainer = jQuery('.booking_form').closest('.elementor-tab-content');
    if(jQuery(currentContainer).find(".returndate").val() == '' && jQuery(currentContainer).find(".bookingdate").val() != '') {
        jQuery('.one-round-tabs-section').find('.elementor-tab-title[data-tab="2"]').addClass('elementor-active').attr({ 'aria-expanded': 'true', tabindex: '0', 'aria-selected': 'true' }).end();
        jQuery('.elementor-tab-content[data-tab="2"]').addClass('elementor-active').css('display', 'block').attr({ 'aria-expanded': 'true', tabindex: '0', 'aria-selected': 'true' }).end();
    
        jQuery('.one-round-tabs-section').find('.elementor-tab-title[data-tab="1"]').removeClass('elementor-active').attr({ 'aria-expanded': 'false', tabindex: '-1', 'aria-selected': 'false' }).end();
        jQuery('.elementor-tab-content[data-tab="1"]').removeClass('elementor-active').css('display', 'none').attr({ 'aria-expanded': 'false', tabindex: '-1', 'aria-selected': 'false' }).end();     
    }
  },1000);

  // Reset search form on sibling items click. 
  jQuery(document).on('click', '.one-round-tabs-section .elementor-tab-title', function () {
    jQuery('.destination_from').val('');
    jQuery('.destination_to').val('');
    jQuery('.bookingdate').val('');
    jQuery('.returndate').val('');
  });

  // Booking form validation. 
  jQuery(".booking_form").each(function(){
    jQuery(this).submit(function(){
    var currentContainer = jQuery(this).closest('.elementor-tab-content');
    jQuery('.destination-from-err').html('');
    jQuery('.destination-to-err').html('');
    jQuery('.booking-date-err').html('');
    //jQuery('.return-date-err').html('');
      if(jQuery(currentContainer).find(".destination_from").val() == '') { 
      jQuery('.destination-from-err').html('<label for="destination_from" class="error">Please select boarding point</label>');	  
      return false;
    }
      if(jQuery(currentContainer).find(".destination_to").val() == '') { 
      jQuery('.destination-to-err').html('<label for="bookingdate" class="error">Please select destination point</label>');	  
      return false;
      }
      if(jQuery(currentContainer).find(".bookingdate").val() == '') { 
      jQuery('.booking-date-err').html('<label for="bookingdate" class="error">Please enter your journey date</label>');	  
      return false;
      }
    });
  });
  

  // Get desitnation detail in search form using ajax call.
  jQuery(".destination_from").change(function () {
    let termId = jQuery(this).val();
    let currentEvent = jQuery(this);
    jQuery.ajax({
      type: 'POST',
      url: ajaxObj.ajax_url,
      data: {
        action: "get_destination_details_callback",
        security: ajaxObj.ajax_nonce,
        term_id: termId
      },
      beforeSend: function () {
        // setting a timeout
        currentEvent.closest('section').addClass('section-loader');
        currentEvent.closest('section').append('<div class="search-loader-main"><div class="search-loader"></div></div>');
      },
      success: function (data) {
        currentEvent.closest('section').removeClass('section-loader');
        jQuery(".destination_to").removeAttr("disabled");
        jQuery(".search-loader-main").remove();
        jQuery(".destination_to").html(data);
      },
      error: function (data) {
        currentEvent.closest('section').removeClass('section-loader');
        jQuery(".search-loader-main").remove();
        alert('Something went wrong! Please try again later');
      },
    });
  });

  jQuery('.mtrap-swap-cities a').click(function() {
    var $section = jQuery(this).closest('section');
    
    $section.addClass('section-loader-swap');
    var fromValue = jQuery('.destination_from').val();
    var toValue = jQuery('.destination_to').val();

    jQuery('.destination_from').val(toValue).change();
    setTimeout(function() {
      jQuery('.destination_to').val(fromValue);
        $section.removeClass('section-loader-swap');
    }, 6000);
  });

  if (jQuery(".booking_form").length > 0) { 
    // remove button if stock count is zero.
    jQuery('.vacant-seats-count').each(function() {
      var vacantSeatsCount = jQuery(this).text().trim(); 
      if (parseInt(vacantSeatsCount) === 0) {
          jQuery(this).parents('.list-item').next('.book_now_fx').find('.mtrap_bus_listing_continue_btn').remove();
          jQuery(this).parents('.list-item').next('.book_now_fx').find('.info_table').append('<div class="buttn_continue">'+ ajaxObj.mtrap_bus_full +'</div>');
      }
    }); 

    // remove seat class option if stock count is zero.
    if (jQuery(".mtrap-seat-class-vacant .seat-vacancy").length > 0) {
      jQuery('.book_tckt_form').find('.mtrap-seat-class-vacant .seat-vacancy').each(function() {
        var pricevalue = jQuery(this).val();
        var priceClassName = jQuery(this).attr('name');
        jQuery(this).parents('.book_now_fx').find('.mtrap_sc_seat_class_selection option').each(function() {
          if (priceClassName == (this). value) { 
            if ( pricevalue <= 0 ){ 
              jQuery(this).remove();
            }
          }
        });
      });
    }
  }
  
  if (jQuery('.bus-list-items').length > 0) {
    var dataPricesArray = [];
    jQuery('.list-item').each(function() {
        var postId = jQuery(this).data('post-id');
        var $bookNowFx = jQuery(this).next('.book_now_fx');
        var basePrice = $bookNowFx.find('.base-price').text().trim();
        var finalPrice = $bookNowFx.find('.mtrap-final-price').text().trim();

        var data = {
            postId: postId,
            basePrice: basePrice,
            finalPrice: finalPrice
        };
        dataPricesArray.push(data);
    });
  }

  jQuery(document).on('change', '.mtrap_sc_seat_class_selection', function () {
    let passenger_seat_class = jQuery(this).val(); 
    let passenger_count = parseInt(jQuery(this).closest('.book_now_fx').find('.mtrap_sc_passenger').val());
    let passengerCountBc = parseInt(jQuery(this).closest('.book_now_fx').prev('.list-item').find('.vacant-seats-count').text().trim());
    
    if (passenger_count > passengerCountBc && passenger_seat_class === '') {
        alert(ajaxObj.mtrap_total_seats + ' = ' + passengerCountBc);
        jQuery(this).closest('.book_now_fx').find('.mtrap_sc_passenger').val('1');
        setTimeout(() => {
            jQuery(this).closest('.book_now_fx').find('.passenger_outer_div').children(".passenger_details_outer").slice(1).remove();
            resetPrices();
        }, 700);
        return false;
    }

    if (jQuery(".mtrap-seat-class-vacant .seat-vacancy").length > 0) {
      jQuery(this).parents('.book_tckt_form').find('.mtrap-seat-class-vacant .seat-vacancy').each(function() {
        let priceClassName = jQuery(this).attr('name');
        let seatClassValue = jQuery(this).closest('.book_now_fx').find('.mtrap_sc_seat_class_selection').val();
        if (seatClassValue != '' && seatClassValue == priceClassName) {
          let pricevalue = parseInt(jQuery(this).val());
          if (passenger_count > pricevalue) {
            alert(ajaxObj.mtrap_passenger_count_alert + ' ' + priceClassName + ' = ' + pricevalue);
            jQuery(this).closest('.book_now_fx').find('.mtrap_sc_seat_class_selection').val('1');
            setTimeout(() => {
                jQuery(this).closest('.book_now_fx').find('.passenger_outer_div').children(".passenger_details_outer").slice(1).remove();
                resetPrices();
            }, 700);
            return false;
          }
        }
      });
    }
});

jQuery(document).on('change', '.mtrap_sc_passenger', function () {
    let passenger_count = parseInt(jQuery(this).val());
    let passengerCountBc = parseInt(jQuery(this).closest('.book_now_fx').prev('.list-item').find('.vacant-seats-count').text().trim());
    
    if (passenger_count > passengerCountBc && jQuery(this).closest('.book_now_fx').find('.mtrap_sc_seat_class_selection').val() == '') {
      alert(ajaxObj.mtrap_total_seats + ' = ' + passengerCountBc );
      jQuery(this).closest('.book_now_fx').find('.mtrap_sc_passenger').val('1');
      setTimeout(() => {
          jQuery(this).closest('.book_now_fx').find('.passenger_outer_div').children(".passenger_details_outer").slice(1).remove();
          resetPrices();
      }, 700);
      return false;
    }
    
    if (jQuery(".mtrap-seat-class-vacant .seat-vacancy").length > 0) {
      jQuery(this).parents('.book_tckt_form').find('.mtrap-seat-class-vacant .seat-vacancy').each(function() {
          let priceClassName = jQuery(this).attr('name');
          let seatClassValue = jQuery(this).closest('.book_now_fx').find('.mtrap_sc_seat_class_selection').val();
          if (seatClassValue != '' && seatClassValue == priceClassName) {
              let pricevalue = parseInt(jQuery(this).val());
              if (passenger_count > pricevalue) {
                  alert(ajaxObj.mtrap_passenger_count_alert + ' ' + priceClassName + ' = ' + pricevalue);
                  jQuery(this).closest('.book_now_fx').find('.mtrap_sc_seat_class_selection').val('1');
                  setTimeout(() => {
                      jQuery(this).closest('.book_now_fx').find('.passenger_outer_div').children(".passenger_details_outer").slice(1).remove();
                      resetPrices();
                  }, 700);
                  return false;
              }
          }
      });
    }
    
    let passenger_field = '';
    let x = 1;
    
    for (let i = 0; i < passenger_count; i++) {
        passenger_field += '<div class="passenger_details_outer">';
        passenger_field += '<label>Passenger - ' + x + '</label>';
        passenger_field += '<div class="passenger_details">';
        passenger_field += '<div class="book_form_fx mtrap_fullname">';
        passenger_field += '<input type="text" name="Fullname[]" class="fullname" placeholder="' + ajaxObj.mtrap_full_name + '">';
        passenger_field += '</div>';
        passenger_field += '<div class="book_form_fx mtrap_email">';
        passenger_field += '<input type="email" name="Email[]" class="mtrap_passenger_email fill_inited" placeholder="Email">';
        passenger_field += '</div>';
        passenger_field += '<div class="book_form_fx mtrap_phone">';
        passenger_field += '<input type="text" name="Phone[]" class="mtrap_passenger_phone fill_inited" placeholder="Phone ">';
        passenger_field += '</div>';
        passenger_field += '<div class="book_form_fx gender-dropdown">';
        passenger_field += '<div class="select_container mtrap_gender">';
        passenger_field += '<select name="gender[]" class="mtrap_passenger_gender">';
        passenger_field += '<option value="" selected disabled hidden>' + ajaxObj.mtrap_gender + '</option>';
        passenger_field += '<option value="Male">' + ajaxObj.mtrap_male + '</option>';
        passenger_field += '<option value="Female">' + ajaxObj.mtrap_female + '</option>';
        passenger_field += '<option value="Other">' + ajaxObj.mtrap_other + '</option>';
        passenger_field += '</select>';
        passenger_field += '</div>';
        passenger_field += '</div>';
        passenger_field += '<div class="book_form_fx adult-child">';
        passenger_field += '<div class="select_container mtrap_pessagnertype">' + mtrap_passenger_container + '</div>';
        passenger_field += '</div>';
        passenger_field += '</div>';
        passenger_field += '</div>';
        x++;
    }
    
    jQuery(this).closest('.book_form_fx').next('.passenger_outer_div').html(passenger_field);
});

function resetPrices() {
    dataPricesArray.forEach(function(data) {
        var postId = data.postId;
        var basePrice = data.basePrice;
        var finalPrice = data.finalPrice;

        jQuery('.list-item[data-post-id="' + postId + '"]').each(function() {
            jQuery(this).next('.book_now_fx').find('.base-price').text(basePrice);
            jQuery(this).next('.book_now_fx').find('.mtrap-final-price').text(finalPrice);
            jQuery(this).next('.book_now_fx').find('.mtrap_sc_seat_class_selection').val(jQuery(".mtrap_sc_seat_class_selection option:first").val());
            jQuery(this).next('.book_now_fx').find('.seat-title').text(' - ');
            jQuery(this).next('.book_now_fx').find('.mtrap_sc_passenger').val(jQuery(".mtrap_sc_passenger option:first").val());
        });
    });
}

// Bus booking flow - On continue click ajax call.
jQuery(document).on('click', '.mtrap_bus_listing_continue_btn', function () {
  var passengersArrContainer = [];
  let passengerTypeArrContainer = [];
  let passengerPricesArrContainer = [];
  var passengerCounts = {};
  let journeyFromCity = jQuery('.destination_from').val();
  let journeyToCity = jQuery('.destination_to').val();
  let journeyOnDate = jQuery('.bookingdate').val();
  let journeyReturnDate = jQuery('.returndate').val();
  let currentContainer = jQuery(this).closest('.book_now_fx');
  let getCurrentPostID = jQuery(currentContainer).prev('.list-item').data('post-id');
  let selectedSeatClassValue = jQuery(currentContainer).find('.mtrap_sc_seat_class_selection').val();

  // get pricing values from class using loop.
  jQuery(currentContainer).find('.mtrap-all-prices').children('.price-settings').each(function() {
    var pricekey = jQuery(this).attr('name');
    var pricevalue = jQuery(this).val();
    passengerPricesArrContainer.push( {
      [pricekey] : pricevalue,
    });
  });
  jQuery(currentContainer).find('.mtrap_passenger_type').each(function() {
    selectedPassengerType = jQuery(this).val(); 
    passengerTypeArrContainer.push( {
      selectedPassengerType,
    });
  });

  // get count of each passenger type
  jQuery.each(passengerTypeArrContainer, function(index, value){
    if(passengerCounts[value.selectedPassengerType] === undefined) {
      passengerCounts[value.selectedPassengerType] = 1;
    } else {
      passengerCounts[value.selectedPassengerType]++;
    }
  });

    jQuery( jQuery(currentContainer).find('.passenger_details_outer') ).each(function( ) {

      let passengerName = jQuery(this).children('.passenger_details').find('.mtrap_fullname input').val();
      let passengerEmail = jQuery(this).children('.passenger_details').find('.mtrap_email input').val();
      let passengerPhone = jQuery(this).children('.passenger_details').find('.mtrap_phone input').val();
      let passengerGender = jQuery(this).children('.passenger_details').find('.mtrap_gender option:selected').val();
      let passengerType = jQuery(this).children('.passenger_details').find('.mtrap_pessagnertype option:selected').val();
      
      passengersArrContainer.push( {
        passenger_name: passengerName,
        passenger_email: passengerEmail,
        passenger_phone: passengerPhone,
        passenger_gender: passengerGender,
        passenger_type: passengerType
      });
    });

    let ContainerArr = JSON.stringify(passengersArrContainer);
    let ContainerPassengers = JSON.stringify(passengerTypeArrContainer);

    jQuery.ajax({
      type: 'POST',
      url: ajaxObj.ajax_url,
      data: {
        action: "get_booking_details_fetch_to_cart",
        security: ajaxObj.ajax_nonce,
        post_id: getCurrentPostID,
        journey_from: journeyFromCity,
        journey_to: journeyToCity,
        journey_date: journeyOnDate,
        journey_return_date: journeyReturnDate,
        seat_class: selectedSeatClassValue,
        passenger_data: ContainerArr,
        passenger_count: passengerTypeArrContainer.length,
        passenger_types: ContainerPassengers
      },
      beforeSend: function () {
        currentContainer.addClass('section-loader');
        currentContainer.append('<div class="search-loader-main"><div class="search-loader"></div></div>');
      },
      success: function (data) {
        var response = jQuery.parseJSON(data);
        if (response.return) {
          jQuery('.destination_from').val(response.journey_to).change();
          jQuery('.bookingdate').val(journeyReturnDate);
          jQuery('.returndate').val('');
          setTimeout(
            function () {
              jQuery('.destination_to').val(response.journey_from).change();
              jQuery('.booking_form').submit();
            }, 10000);

        } else if (response.error) {
          alert(response.error);
          currentContainer.removeClass('section-loader');
          jQuery('.search-loader-main').remove();
          return false;
        } else {
          window.location.href = response.checkout;
        }
      },
      error: function () {
        currentContainer.removeClass('section-loader');
        jQuery('.search-loader-main').remove();
        alert('Something went wrong! Please try again!');
        location.reload();
      },
    });
  });
});


// transportation order modification page start.
jQuery(document).ready(function () {
  if (jQuery(".mtrap-ticket-modification-outer").length > 0) {
    // journey date datepicker.
    jQuery(".journeydate").datepicker({
      minDate: new Date(),
      dateFormat: 'dd-mm-yy',
      numberOfMonths: 1,
      onSelect: function (selected) {
        let selectedDt = selected.split(/\s*\-\s*/g);
        let DtFormat = selectedDt[1] + '/' + selectedDt[0] + '/' + selectedDt[2];
        let dt = new Date(DtFormat);
        dt.setDate(dt.getDate());
      }
    });

    // change journey date datepicker.
    jQuery(".changejourneydate").datepicker({
      minDate: new Date(),
      dateFormat: 'dd-mm-yy',
      numberOfMonths: 1,
      onSelect: function (selected) {
        let selectedDt = selected.split(/\s*\-\s*/g);
        let DtFormat = selectedDt[1] + '/' + selectedDt[0] + '/' + selectedDt[2];
        let dt = new Date(DtFormat);
        dt.setDate(dt.getDate());
      }
    });

    // Booking form validation. 
    jQuery(".transportation_ticket_modification_form").each(function(){
      jQuery(this).submit(function(){
        var currentContainer = jQuery(this).closest('.elementor-tab-content');
        jQuery('.booking-number-err').html('');
        jQuery('.journey-date-err').html('');
        jQuery('.destination-from-om-err').html('');
        jQuery('.destination-to-om-err').html('');
        jQuery('.journey-email-err').html('');
        
        if(jQuery(currentContainer).find(".booking_number").val() == '') { 
          jQuery('.booking-number-err').html('<label for="booking_number" class="error">Please enter booking number!</label>');	  
          return false;
        }
        if(jQuery(currentContainer).find(".destination_from").val() == '') { 
          jQuery('.destination-from-om-err').html('<label for="bookingdate" class="error">Please enter your journey date!</label>');	  
          return false;
        }
        if(jQuery(currentContainer).find(".destination_to").val() == '') { 
          jQuery('.destination-to-om-err').html('<label for="destination_to" class="error">Please enter your destination!</label>');	  
          return false;
        }
        if(jQuery(currentContainer).find(".journeyemail").val() == '') { 
          jQuery('.journey-email-err').html('<label for="journeyemail" class="error">Please enter your journey email!</label>');	  
          return false;
        }
        if(jQuery(currentContainer).find(".journeydate").val() == '') { 
          jQuery('.journey-date-err').html('<label for="journeydate" class="error">Please select journey date!</label>');	  
          return false;
        }
      });
    });
  }
  // ticket cancellation.
  if (jQuery(".form-cancellation").length > 0) {
    jQuery(".journeydate").datepicker({
      minDate: new Date(),
      dateFormat: 'dd-mm-yy',
      numberOfMonths: 1,
      onSelect: function (selected) {
        let selectedDt = selected.split(/\s*\-\s*/g);
        let DtFormat = selectedDt[1] + '/' + selectedDt[0] + '/' + selectedDt[2];
        let dt = new Date(DtFormat);
        dt.setDate(dt.getDate());
      }
    });
  }
});

// on modification and cancellation page if url has parameters, add values to the form. 
jQuery(document).ready(function($) {
    function getQueryParams() {
        var params = {};
        var queryString = window.location.search.substring(1);
        var queryArray = queryString.split('&');
        for (var i = 0; i < queryArray.length; i++) {
            var pair = queryArray[i].split('=');
            params[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
        }
        return params;
    }

    setTimeout(function() {
        if ($('.transportation_ticket_modification_form').length > 0 || $('.transportation_ticket_cancellation_form').length > 0) {
            var params = getQueryParams();

            if (params.booking_number && params.destination_from && params.destination_to && params.journeyemail && params.journeydate) {
                $('input.booking_number').val(params.booking_number);
                $('select.destination_from').val(params.destination_from);

                var destinationToText = $('select.destination_from option[value="' + params.destination_to + '"]').text();
                
                if (destinationToText) {
                    var $destinationToSelect = $('select.destination_to');
                    $destinationToSelect.empty(); 
                    $destinationToSelect.append('<option value="' + params.destination_to + '">' + destinationToText + '</option>');
                    $destinationToSelect.val(params.destination_to).prop('disabled', false);
                }

                $('input.journeyemail').val(params.journeyemail);
                $('input.journeydate').val(params.journeydate);
            }
        }
    }, 1500);
});