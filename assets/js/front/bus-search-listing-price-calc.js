// Toggle select bus option on click.
jQuery(".select_bus_btn").click(function () {
	var bookNowFx = jQuery(this).parents(".list-item").next(".book_now_fx");
	bookNowFx.slideToggle("slow");
	bookNowFx.siblings(".book_now_fx").slideUp("slow");
});

// count initial price on page load.
jQuery(document).ready(function () {

    jQuery('.bus-list-items').each(function () {
		
        var passengerCount = jQuery(this).children('.list-item').next('.book_now_fx').find('.mtrap_sc_passenger option:selected').val(); 
        let selectedSeatClassValue = jQuery(this).find('.mtrap_sc_seat_class_selection').val();
		if ( jQuery(this).find('.mtrap_sc_seat_class_selection').val() != '' && jQuery(this).find('.mtrap-seat-class-price').children('.class-price').length !== 0
		) {
			jQuery(this).find('.mtrap-seat-class-price').children('.class-price').each(function () {
				if ( selectedSeatClassValue == jQuery(this).attr('data-seat-class') ) {
					window.getSeatClassPercentage = jQuery(this).val();
				}
			});		
		}
		
		if (jQuery(this).find('.mtrap_sc_seat_class_selection').val() == '') {
            jQuery(this).find('.mtrap-seat-class .seat-title').html(' - ');
        } else {
			jQuery(this).find('.mtrap-seat-class .seat-title').html(selectedSeatClassValue);
		}

        // Passenger count module
        if (passengerCount > 0) {
            let getAdultPrice = jQuery(this).children('.list-item').next('.book_now_fx').find('.mtrap-all-prices input.adult_price').val();
            let getPricePercentage = jQuery(this).children('.list-item').next('.book_now_fx').find('.mtrap-all-prices input.price_tax').val();

            let initialBasePrice = getAdultPrice * passengerCount;
            let initialFinalPrice;

            // Check for seat class selection and price calculation
            if (selectedSeatClassValue != '' && jQuery(this).find('.mtrap-seat-class-price').children('.class-price').length !== 0) {
                let seatClassPercentage = 0;

                jQuery(this).find('.mtrap-seat-class-price').children('.class-price').each(function () {
                    if (selectedSeatClassValue == jQuery(this).attr('data-seat-class')) {
                        seatClassPercentage = jQuery(this).val();
                    }
                });

                let initialFinalPriceWithSeatClass = initialBasePrice * (1 + seatClassPercentage / 100);
                initialFinalPrice = getPricePercentage.length !== 0
                    ? initialFinalPriceWithSeatClass * (1 + getPricePercentage / 100)
                    : initialFinalPriceWithSeatClass;

            } else {
                initialFinalPrice = getPricePercentage.length !== 0
                    ? initialBasePrice * (1 + getPricePercentage / 100)
                    : initialBasePrice;
            }

            // Update the HTML for base price and final price
            jQuery(this).children('.list-item').next('.book_now_fx').find('.base-price').html(parseFloat(initialBasePrice).toFixed(2));
            jQuery(this).children('.list-item').next('.book_now_fx').find('.mtrap-final-price').html(parseFloat(initialFinalPrice).toFixed(2));
        }

        if (passengerCount == 0) {
            jQuery(this).children('.list-item').next('.book_now_fx').find('.mtrap-final-price').html('0.00');
        }
    });
});

// count initial price on passenger dropdown selection.
jQuery(document).on('change', '.mtrap_sc_passenger', function () {
	// local variables.
	let initialBasePrice;
	let initialFinalPrice;
	let passengerCount = jQuery(this).val();
	let getAdultPrice = jQuery(this).closest('.book_tckt_form').siblings('.info_table').find('.mtrap-all-prices input.adult_price').val();
	let getPricePercentage = jQuery(this).closest('.book_tckt_form').siblings('.info_table').find('.mtrap-all-prices input.price_tax').val();
	let currentContainer = jQuery(this).closest('.book_now_fx');

	let selectedSeatClassValue = jQuery(currentContainer).find('.mtrap_sc_seat_class_selection').val();

	initialBasePrice = getAdultPrice * passengerCount;
	if ( jQuery(currentContainer).find('.mtrap_sc_seat_class_selection').val() == '' ) {
		jQuery(this).closest('.book_tckt_form').siblings('.info_table').find('.mtrap-seat-class .seat-title').html(' - ');
	}
	if ( jQuery(currentContainer).find('.mtrap_sc_seat_class_selection').val() != '' && jQuery(currentContainer).find('.mtrap-seat-class-price').children('.class-price').length !== 0
	) {
		jQuery(currentContainer).find('.mtrap-seat-class-price').children('.class-price').each(function () {
			if ( selectedSeatClassValue == jQuery(this).attr('data-seat-class') ) {
				window.getSeatClassPercentage = jQuery(this).val();
			}
		});
		initialFinalPriceWithSeatClass = initialBasePrice;

		if (getPricePercentage.length !== 0) {
			initialFinalPriceWithSeatClass *= 1 + getSeatClassPercentage / 100;
			initialFinalPriceWithSeatClass *= 1 + getPricePercentage / 100;
			initialFinalPrice = initialFinalPriceWithSeatClass;
		} else {
			initialFinalPriceWithSeatClass *= 1 + getSeatClassPercentage / 100;
			initialFinalPrice = initialFinalPriceWithSeatClass;
		}
	} else {
		if (getPricePercentage.length !== 0) {
			initialFinalPriceWithTax = initialBasePrice;
			initialFinalPriceWithTax *= 1 + getPricePercentage / 100;
			initialFinalPrice = initialFinalPriceWithTax;
		} else {
			initialFinalPrice = initialBasePrice;
		}
	}

	// change html of classes
	jQuery(this).closest('.book_tckt_form').siblings('.info_table').find('.base-price').html(parseFloat(initialBasePrice).toFixed(2));
	jQuery(this).closest('.book_tckt_form').siblings('.info_table').find('.mtrap-final-price').html(parseFloat(initialFinalPrice).toFixed(2));
});

// count final price based on passenger type dropdown selection.
jQuery(document).on('change', '.mtrap_passenger_type', function () {
	let passengerTypeArrContainer = [];
	let passengerPricesArrContainer = [];
	var passengerCounts = {};
	var finalBasePrice = 0;
	var totalPriceAfterPassengerCount = [];
	let currentContainer = jQuery(this).closest('.book_now_fx');
	let getPricePercentage = jQuery(this).closest('.book_tckt_form').siblings('.info_table').find('.mtrap-all-prices input.price_tax').val();
	let selectedSeatClassValue = jQuery(currentContainer).find('.mtrap_sc_seat_class_selection').val();

	passengerTypeArrContainer = jQuery(currentContainer)
		.find('.mtrap_passenger_type')
		.map(function () {
			let selectedAdultChild = jQuery(this).find(':selected').val();
			return selectedAdultChild;
		});

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

	// get price of each passenger type
	jQuery.each(passengerCounts, function(keyCustomerType, countCustomers){
		jQuery.each(passengerPricesArrContainer, function(key, value){
			if (value[keyCustomerType] !== undefined) {
				customerCalcInitial = countCustomers * value[keyCustomerType];
				totalPriceAfterPassengerCount.push( {
					customerCalcInitial,
				});
			}
		});
	});
	
	for ( passengerPriceItereation = 0; passengerPriceItereation < totalPriceAfterPassengerCount.length; passengerPriceItereation++ ) { 
		finalBasePrice += totalPriceAfterPassengerCount[passengerPriceItereation].customerCalcInitial;
	}

	if (
		jQuery(currentContainer)
			.find('.mtrap_sc_seat_class_selection')
			.val() == ''
	) {
		jQuery(this)
			.closest('.book_tckt_form')
			.siblings('.info_table')
			.find('.mtrap-seat-class .seat-title')
			.html(' - ');
	}
	if (
		jQuery(currentContainer)
			.find('.mtrap_sc_seat_class_selection')
			.val() != '' &&
		jQuery(currentContainer)
			.find('.mtrap-seat-class-price')
			.children('.class-price').length !== 0
	) {
		jQuery(currentContainer)
			.find('.mtrap-seat-class-price')
			.children('.class-price')
			.each(function () {
				if (
					selectedSeatClassValue ==
					jQuery(this).attr('data-seat-class')
				) {
					window.getSeatClassPercentage = jQuery(this).val();
				}
			});

		initialFinalPriceWithSeatClass = finalBasePrice;

		if (getPricePercentage.length !== 0) {
			initialFinalPriceWithSeatClass *=
				1 + getSeatClassPercentage / 100;
			initialFinalPriceWithSeatClass *= 1 + getPricePercentage / 100;
			finalPrice = initialFinalPriceWithSeatClass;
		} else {
			initialFinalPriceWithSeatClass *=
				1 + getSeatClassPercentage / 100;
			finalPrice = initialFinalPriceWithSeatClass;
		}
	} else {
		if (getPricePercentage.length !== 0) {
			initialFinalPriceWithTax = finalBasePrice;
			initialFinalPriceWithTax *= 1 + getPricePercentage / 100;
			finalPrice = initialFinalPriceWithTax;
		} else {
			finalPrice = finalBasePrice;
		}
	}

	// change html of classes
	jQuery(this).closest('.book_tckt_form').siblings('.info_table').find('.base-price').html(parseFloat(finalBasePrice).toFixed(2));
	jQuery(this).closest('.book_tckt_form').siblings('.info_table').find('.mtrap-final-price').html(parseFloat(finalPrice).toFixed(2));
});

// count final price based on passenger type dropdown selection.
jQuery(document).on( 'change', '.mtrap_sc_seat_class_selection', function () { 
	let passengerTypeArrContainer = [];
	let passengerPricesArrContainer = [];
	var passengerCounts = {};
	var totalPriceAfterPassengerCount = [];
	var finalBasePrice = 0;
	let finalTotalAfterClassSelection;
	let selectedSeatClassValue = jQuery(this).val();
	let selectedSeatClassText = jQuery(this).find(':selected').text();
	let currentContainer = jQuery(this).closest('.book_now_fx');
	let getPricePercentage = jQuery(this).closest('.book_tckt_form').siblings('.info_table').find('.mtrap-all-prices input.price_tax').val();

	jQuery(this).closest('.book_tckt_form').siblings('.info_table').find('.mtrap-seat-class span.seat-title').html(selectedSeatClassText.replace(/-/g, ' '));

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

	// get price of each passenger type
	jQuery.each(passengerCounts, function(keyCustomerType, countCustomers){
		jQuery.each(passengerPricesArrContainer, function(key, value){
			if (value[keyCustomerType] !== undefined) {
				customerCalcInitial = countCustomers * value[keyCustomerType];
				totalPriceAfterPassengerCount.push( {
					customerCalcInitial,
				});
			}
		});
	});
	
	for ( passengerPriceItereation = 0; passengerPriceItereation < totalPriceAfterPassengerCount.length; passengerPriceItereation++ ) { 
		finalBasePrice += totalPriceAfterPassengerCount[passengerPriceItereation].customerCalcInitial;
	}
	
	if (
		jQuery(currentContainer).find('.mtrap_sc_seat_class_selection').val() == ''
	) {
		jQuery(this).closest('.book_tckt_form').siblings('.info_table').find('.mtrap-seat-class .seat-title').html(' - ');
	}

	if (
		jQuery(currentContainer).find('.mtrap_sc_seat_class_selection').val() != '' &&
		jQuery(currentContainer).find('.mtrap-seat-class-price').children('.class-price').length !== 0
	) {
		jQuery(currentContainer).find('.mtrap-seat-class-price').children('.class-price').each(function () {
			if ( selectedSeatClassValue == jQuery(this).attr('data-seat-class') ) {
				window.getSeatClassPercentage = jQuery(this).val();
			}
		});

		initialFinalPriceWithSeatClass = finalBasePrice;

		if (getPricePercentage.length !== 0) {
			initialFinalPriceWithSeatClass *= 1 + getSeatClassPercentage / 100;
			initialFinalPriceWithSeatClass *= 1 + getPricePercentage / 100;
			finalTotalAfterClassSelection = initialFinalPriceWithSeatClass;
		} else {
			initialFinalPriceWithSeatClass *= 1 + getSeatClassPercentage / 100;
			finalTotalAfterClassSelection = initialFinalPriceWithSeatClass;
		}
	} else {
		if (getPricePercentage.length !== 0) {
			initialFinalPriceWithTax = finalBasePrice;
			initialFinalPriceWithTax *= 1 + getPricePercentage / 100;
			finalTotalAfterClassSelection = initialFinalPriceWithTax;
		} else {
			finalTotalAfterClassSelection = finalBasePrice;
		}
	}
	jQuery(this).closest('.book_tckt_form').siblings('.info_table').find('.mtrap-final-price').html( parseFloat(finalTotalAfterClassSelection).toFixed(2));
	}
);

