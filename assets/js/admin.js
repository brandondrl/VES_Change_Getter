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
                        // Añadir un retraso mínimo para asegurar que la notificación de carga sea visible
                        setTimeout(() => {
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
                        }, 1000); // Retraso de 1 segundo
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
        
        // Inicialización de la configuración de Toast para SweetAlert2
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-right',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            customClass: {
                popup: 'swal-toast-popup'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
                // Añadir margen superior al toast para evitar que se corte
                toast.style.marginTop = '20px'
            }
        });

        // Event listener para los botones de copiar
        $('.ves-copy-button').on('click', function() {
            Toast.fire({
                icon: 'success',
                title: 'Copiado al portapapeles'
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
            const $card = $(this).closest('.w-full');
            const $jsonContent = $card.find('.json-content');
            $jsonContent.slideToggle(200);
        });

        // Función para mostrar notificaciones de cambio de tasa
        function showRateChangeNotification(type, oldValue, newValue) {
            const diff = newValue - oldValue;
            const icon = diff > 0 ? 'warning' : (diff < 0 ? 'error' : 'info');
            const diffText = diff > 0 ? `+${diff.toFixed(2)}` : diff.toFixed(2);
            
            Toast.fire({
                icon: icon,
                title: `Tasa ${type}: ${diffText} Bs.`
            });
        }

        // Exponer la función a window para poder usarla desde PHP
        window.showRateChangeNotification = showRateChangeNotification;
    });
})(jQuery); 