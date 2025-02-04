(function ($) {
    $(document).ready(function () {
       
        let partialCheckoutEnabled = wcpc_settings.enable_partial_checkout === 'yes';
        

        if (!partialCheckoutEnabled) {
            $('.wcpc-notice').hide();
            
            return;
        }

        function fetchProductIDs() {
            $.ajax({
                type: 'POST',
                url: wcpc_ajax.ajax_url,
                data: { action: 'wcpc_get_cart_items' },
                success: function (response) {

                    if (response.success && Array.isArray(response.data.cart_items)) {
                        attachProductIDs(response.data.cart_items);
                    } else {
                        console.log("No cart items received or invalid response format.");
                    }
                },
                error: function (xhr, status, error) {
                    console.log("AJAX Error:", status, error, xhr.responseText);
                }
            });
        }

        function attachProductIDs(cartItems) {
            $('.wc-block-cart-items__row, .cart_item').each(function () {
                let row = $(this);
                let productName = row.find('.wc-block-components-product-name, .product-name').text().trim();

                let matchedItem = cartItems.find(item => item.name === productName);

                if (matchedItem) {
                    row.attr('data-product-id', matchedItem.product_id);
                    console.log(` Attached product_id: ${matchedItem.product_id} to ${productName}`);
                } else {
                    console.log(` No match found for ${productName}`);
                }
            });

            addCheckboxes();
        }

        function addCheckboxes() {
            $('.wc-block-cart-items__row, .cart_item').each(function () {
                let row = $(this);
                let productId = row.attr('data-product-id');

                if (!productId) {
                    console.log(" Missing Product ID for a row:", row);
                    return;
                }

                if (row.find('.wcpc-select-item').length === 0) {
                    let checkbox = `<input type="checkbox" class="wcpc-select-item" data-product-id="${productId}">`;
                    row.find('.wc-block-cart-item__image, .product-thumbnail').prepend(checkbox);
                }
            });

            restoreSelectedCheckboxes();
        }

        function restoreSelectedCheckboxes() {
            $.ajax({
                type: 'POST',
                url: wcpc_ajax.ajax_url,
                data: { action: 'wcpc_get_selected_items' },
                success: function (response) {
                    if (response.success) {
                        let selectedItems = response.selected_items;

                        $('.wcpc-select-item').each(function () {
                            let productId = $(this).data('product-id');
                            if (selectedItems.includes(productId.toString())) {
                                $(this).prop('checked', true);
                            }
                        });
                    }
                },
                error: function () {
                    console.log("Error fetching selected items.");
                }
            });
        }

        $(document).on('change', '.wcpc-select-item', function () {
            let selectedItems = [];
            $('.wcpc-select-item:checked').each(function () {
                selectedItems.push($(this).data('product-id'));
            });

            console.log("Saving Selected Items:", selectedItems);

            $.ajax({
                type: 'POST',
                url: wcpc_ajax.ajax_url,
                data: {
                    action: 'wcpc_save_selected_items',
                    selected_items: selectedItems
                },
                success: function (response) {
                    console.log("Session Updated:", response);
                },
                error: function () {
                    console.log("Error updating selected items.");
                }
            });
        });

        $(window).on('load', function () {
            setTimeout(fetchProductIDs, 500);
        });

        $(document.body).on('wc-blocks-cart-items-updated wc-fragments-refreshed updated_cart_totals', function () {
            setTimeout(fetchProductIDs, 300);
        });
    });
})(jQuery);


(function ($) {
    $(document).ready(function () {

        function isCartPage() {
            return window.location.href.includes("cart");
        }

        function restoreFullCart() {
            console.log("Attempting to restore full cart...");
            $.ajax({
                type: "POST",
                url: wcpc_ajax.ajax_url,
                data: { action: "wcpc_restore_cart" },
                success: function (response) {
                    console.log("Full Cart Restore Response:", response);
                    if (response.success) {
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    } else {
                        console.log("Skipping Cart Restore:", response.message);
                    }
                },
                error: function () {
                    console.log(" Failed to restore full cart.");
                },
            });
        }

        if (isCartPage()) {
            restoreFullCart();
        }
    });
})(jQuery);


(function ($) {
    $(document).ready(function () {
        console.log("🚀 WCPC script is running...");

        function updateRowHighlighting() {
            $('.wcpc-select-item').each(function () {
                let row = $(this).closest('.wc-block-cart-items__row, .cart_item');

                if ($(this).is(':checked')) {
                    row.addClass('wcpc-selected'); // ✅ Add highlight
                } else {
                    row.removeClass('wcpc-selected'); // ❌ Remove highlight if unchecked
                }
            });
        }

        // Listen for changes on checkboxes
        $(document).on('change', '.wcpc-select-item', function () {
            updateRowHighlighting();
        });

        // Run highlighting on page load
        $(window).on('load', function () {
            setTimeout(updateRowHighlighting, 500);
        });

    });
})(jQuery);
