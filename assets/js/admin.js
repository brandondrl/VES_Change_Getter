/**
 * VES Change Getter Admin JS
 */
(function($) {
    'use strict';
    
    // DOM Ready
    $(function() {
        // Fetch data from API button
        $('#ves-change-getter-fetch-btn').on('click', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $statusMessage = $('#ves-change-getter-status-message');
            
            // Show loading state
            $button.prop('disabled', true).addClass('opacity-50');
            $button.find('.loading-indicator').show();
            $button.find('.button-text').text('Actualizando datos...');
            
            // Clear previous messages
            $statusMessage.removeClass('bg-green-100 text-green-800 bg-red-100 text-red-800').empty();
            
            // Make AJAX request
            $.ajax({
                url: ves_change_getter.ajax_url,
                type: 'POST',
                data: {
                    action: 'fetch_rates_data',
                    nonce: ves_change_getter.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $statusMessage.addClass('bg-green-100 text-green-800 p-4 rounded-md mb-4')
                            .html('<p>' + response.data.message + '</p>');
                            
                        // Reload the page after a short delay to show updated data
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        $statusMessage.addClass('bg-red-100 text-red-800 p-4 rounded-md mb-4')
                            .html('<p>' + response.data.message + '</p>');
                            
                        // Reset button
                        $button.prop('disabled', false).removeClass('opacity-50');
                        $button.find('.loading-indicator').hide();
                        $button.find('.button-text').text('Actualizar datos');
                    }
                },
                error: function() {
                    $statusMessage.addClass('bg-red-100 text-red-800 p-4 rounded-md mb-4')
                        .html('<p>Error al comunicarse con el servidor.</p>');
                        
                    // Reset button
                    $button.prop('disabled', false).removeClass('opacity-50');
                    $button.find('.loading-indicator').hide();
                    $button.find('.button-text').text('Actualizar datos');
                }
            });
        });
        
        // Format date columns in the table
        $('.ves-change-getter-date').each(function() {
            const dateStr = $(this).text();
            if (dateStr) {
                try {
                    const date = new Date(dateStr);
                    if (!isNaN(date)) {
                        const formattedDate = date.toLocaleString('es-ES', {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        $(this).text(formattedDate);
                    }
                } catch(e) {
                    // Keep original format if error
                }
            }
        });
        
        // Collapsible JSON viewer
        $('.json-toggle').on('click', function() {
            const $this = $(this);
            const $jsonContent = $this.siblings('.json-content');
            
            $jsonContent.toggleClass('hidden');
            
            // Toggle icon/text
            if ($jsonContent.hasClass('hidden')) {
                $this.html('<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg> Mostrar JSON');
            } else {
                $this.html('<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg> Ocultar JSON');
            }
        });
    });
})(jQuery); 