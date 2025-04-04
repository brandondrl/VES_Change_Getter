<div class="wrap ves-change-getter-admin-wrap">
    <h1><?php echo esc_html__('VES Change Getter', 'ves-change-getter'); ?></h1>
    
    <div id="ves-change-getter-status-message"></div>
    
    <div class="flex flex-wrap -mx-2 mb-6">
        <!-- Current Rates Card -->
        <div class="w-full px-2 mb-4 lg:w-1/2">
            <div class="ves-change-getter-card">
                <div class="ves-change-getter-card__header">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900"><?php echo esc_html__('Tasas Actuales', 'ves-change-getter'); ?></h2>
                            <p class="text-sm text-gray-600"><?php echo esc_html__('Datos más recientes de la API', 'ves-change-getter'); ?></p>
                        </div>
                        <button id="ves-change-getter-fetch-btn" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-wider hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <span class="loading-indicator hidden">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span class="button-text"><?php esc_html_e('Actualizar datos', 'ves-change-getter'); ?></span>
                        </button>
                    </div>
                </div>
                <div class="ves-change-getter-card__body">
                    <?php if ($latest_rate): ?>
                        <div>
                            <p class="text-sm text-gray-500 mb-4">
                                <span class="font-medium text-gray-900"><?php esc_html_e('Última actualización:', 'ves-change-getter'); ?></span>
                                <span class="ves-change-getter-date"><?php echo esc_html($latest_rate['fecha']); ?></span>
                            </p>
                        </div>
                        
                        <?php if (isset($latest_rate['json_decoded']['rates'])): ?>
                            <div class="flex flex-wrap gap-4">
                                <?php 
                                $rates = $latest_rate['json_decoded']['rates'];
                                $rate_types = [
                                    'bcv' => [
                                        'title' => 'BCV',
                                        'color' => 'blue'
                                    ],
                                    'parallel' => [
                                        'title' => 'Paralelo',
                                        'color' => 'green'
                                    ],
                                    'average' => [
                                        'title' => 'Promedio',
                                        'color' => 'purple'
                                    ]
                                ];
                                
                                // Orden solicitado
                                $display_order = ['bcv', 'average', 'parallel'];
                                
                                foreach ($display_order as $type):
                                    if (isset($rates[$type])):
                                        $info = $rate_types[$type];
                                ?>
                                <div class="flex-1 p-4 bg-white rounded-lg shadow-md text-center flex flex-col items-center justify-center">
                                    <!-- Icono específico para cada tipo de rate -->
                                    <div class="rounded-full p-3 mb-4" style="background-color: <?php 
                                        if ($type === 'bcv') {
                                            echo '#bfdbfe'; // bg-blue-200 color
                                        } elseif ($type === 'average') {
                                            echo '#dcfce7'; // bg-green-100 color
                                        } else {
                                            echo '#fee2e2'; // bg-red-100 color
                                        }
                                    ?>;">
                                        <?php if ($type === 'bcv'): ?>
                                            <span class="dashicons dashicons-bank" style="font-size: 32px; width: 32px; height: 32px; color: #2563eb;"></span>
                                        <?php elseif ($type === 'average'): ?>
                                            <span class="dashicons dashicons-calculator" style="font-size: 32px; width: 32px; height: 32px; color: #16a34a;"></span>
                                        <?php elseif ($type === 'parallel'): ?>
                                            <span class="dashicons dashicons-chart-line" style="font-size: 32px; width: 32px; height: 32px; color: #dc2626;"></span>
                                        <?php endif; ?>
                                    </div>
                                    <dl class="text-center">
                                        <dt class="text-lg font-medium text-gray-700 mb-1">
                                            <?php echo esc_html($info['title']); ?>
                                        </dt>
                                        <dd>
                                            <div class="text-2xl font-bold text-gray-900">
                                                <?php echo esc_html(number_format($rates[$type]['value'], 2, ',', '.')); ?> Bs.
                                            </div>
                                        </dd>
                                        <dd class="mt-2">
                                            <span class="text-sm text-gray-500">
                                                <?php echo esc_html($rates[$type]['catch_date']); ?>
                                            </span>
                                        </dd>
                                    </dl>
                                </div>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                            </div>
                        
                            <div class="mt-4 p-3 bg-gray-50 rounded-md">
                                <button class="json-toggle inline-flex items-center text-sm text-blue-600 hover:text-blue-700 focus:outline-none">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    Mostrar JSON
                                </button>
                                <div class="json-content hidden mt-2 p-3 bg-gray-100 rounded-md overflow-x-auto">
                                    <pre class="text-xs text-gray-800"><?php echo esc_html(json_encode($latest_rate['json_decoded'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="p-4 text-center text-gray-500">
                                <?php esc_html_e('No hay datos de tasas disponibles.', 'ves-change-getter'); ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="p-4 text-center text-gray-500">
                            <?php esc_html_e('No hay datos disponibles. Haga clic en "Actualizar datos" para obtener la información más reciente.', 'ves-change-getter'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- API Info Card -->
        <div class="w-full px-2 mb-4 lg:w-1/2">
            <div class="ves-change-getter-card">
                <div class="ves-change-getter-card__header">
                    <h2 class="text-lg font-semibold text-gray-900"><?php echo esc_html__('Información de API', 'ves-change-getter'); ?></h2>
                    <p class="text-sm text-gray-600"><?php echo esc_html__('Endpoints disponibles para consulta', 'ves-change-getter'); ?></p>
                </div>
                <div class="ves-change-getter-card__body">
                    <div class="space-y-4">
                        <div class="p-3 bg-gray-50 rounded-md">
                            <h3 class="font-medium text-gray-900 mb-2">Endpoint para obtener la tasa más reciente</h3>
                            <p class="mb-2 text-sm text-gray-600">Utilice el siguiente endpoint para obtener los datos más recientes:</p>
                            <div class="flex items-center">
                                <code class="text-sm bg-gray-100 px-2 py-1 rounded-md flex-1"><?php echo esc_url(rest_url('ves-change-getter/v1/latest')); ?></code>
                                <button class="ml-2 p-1 text-gray-500 hover:text-gray-700 focus:outline-none" onclick="navigator.clipboard.writeText('<?php echo esc_url(rest_url('ves-change-getter/v1/latest')); ?>')">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="p-3 bg-gray-50 rounded-md">
                            <h3 class="font-medium text-gray-900 mb-2">Endpoint para obtener historial de tasas</h3>
                            <p class="mb-2 text-sm text-gray-600">Utilice el siguiente endpoint para obtener el historial de tasas:</p>
                            <div class="flex items-center">
                                <code class="text-sm bg-gray-100 px-2 py-1 rounded-md flex-1"><?php echo esc_url(rest_url('ves-change-getter/v1/rates')); ?></code>
                                <button class="ml-2 p-1 text-gray-500 hover:text-gray-700 focus:outline-none" onclick="navigator.clipboard.writeText('<?php echo esc_url(rest_url('ves-change-getter/v1/rates')); ?>')">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-2 text-sm text-gray-600">Parámetros opcionales:</p>
                            <ul class="mt-1 text-xs text-gray-600 space-y-1 ml-4 list-disc">
                                <li><code>start_date</code>: Fecha inicial (formato YYYY-MM-DD)</li>
                                <li><code>end_date</code>: Fecha final (formato YYYY-MM-DD)</li>
                                <li><code>limit</code>: Número máximo de registros (por defecto: 100)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Historical Data Table -->
    <div class="ves-change-getter-card mb-6">
        <div class="ves-change-getter-card__header">
            <h2 class="text-lg font-semibold text-gray-900"><?php echo esc_html__('Historial de Tasas', 'ves-change-getter'); ?></h2>
            <p class="text-sm text-gray-600"><?php echo esc_html__('Registros históricos de tasas de cambio', 'ves-change-getter'); ?></p>
        </div>
        <div class="overflow-x-auto">
            <table class="ves-change-getter-table ves-change-getter-table--striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('ID', 'ves-change-getter'); ?></th>
                        <th><?php esc_html_e('Fecha', 'ves-change-getter'); ?></th>
                        <th><?php esc_html_e('BCV', 'ves-change-getter'); ?></th>
                        <th><?php esc_html_e('Promedio', 'ves-change-getter'); ?></th>
                        <th><?php esc_html_e('Paralelo', 'ves-change-getter'); ?></th>
                        <th><?php esc_html_e('Actualización', 'ves-change-getter'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($all_rates)): ?>
                        <?php foreach ($all_rates as $rate): ?>
                            <tr>
                                <td><?php echo esc_html($rate['id']); ?></td>
                                <td class="ves-change-getter-date"><?php echo esc_html($rate['fecha']); ?></td>
                                <td>
                                    <?php 
                                    if (isset($rate['json_decoded']['rates']['bcv'])) {
                                        echo esc_html(number_format($rate['json_decoded']['rates']['bcv']['value'], 2, ',', '.')) . ' Bs.';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    if (isset($rate['json_decoded']['rates']['average'])) {
                                        echo esc_html(number_format($rate['json_decoded']['rates']['average']['value'], 2, ',', '.')) . ' Bs.';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    if (isset($rate['json_decoded']['rates']['parallel'])) {
                                        echo esc_html(number_format($rate['json_decoded']['rates']['parallel']['value'], 2, ',', '.')) . ' Bs.';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td class="ves-change-getter-date"><?php echo esc_html($rate['update_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-gray-500">
                                <?php esc_html_e('No hay registros disponibles.', 'ves-change-getter'); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div> 