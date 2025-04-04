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
            
            // Show loading state with SweetAlert2
            Swal.fire({
                title: 'Actualizando datos...',
                text: 'Por favor espere mientras obtenemos la información más reciente',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
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
                        Swal.fire({
                            title: '¡Éxito!',
                            text: response.data.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Reload the page to show updated data
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.data.message || 'Ha ocurrido un error.',
                            icon: 'error',
                            confirmButtonText: 'Cerrar'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error al comunicarse con el servidor.',
                        icon: 'error',
                        confirmButtonText: 'Cerrar'
                    });
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