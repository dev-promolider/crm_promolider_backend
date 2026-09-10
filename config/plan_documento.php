<?php

/**
 * El plan de compensacion tal y como se le entrega al afiliado, en cifras.
 *
 * Esto no es configuracion que use la aplicacion para pagar: lo que se paga sale
 * siempre de la base de datos, que es lo que el administrador puede tocar. Esto es
 * la copia del documento contra la que se contrasta, y la usa "php artisan
 * plan:verificar" para decir, punto por punto, si el sistema esta dando lo que el
 * papel promete.
 *
 * Cuando salga el plan nuevo —con nombres distintos, rangos distintos y porcentajes
 * distintos— se actualiza este archivo y se vuelve a pasar el comando. Es el unico
 * sitio que hay que tocar.
 *
 * Fuente: "PLAN DE COMPENSACION PROMOLIDER: tu ruta hacia los ingresos residuales
 * por decadas", 25 paginas, entregado por el equipo el 07/09/2026.
 */

return [

    'version' => 'Plan de pre-lanzamiento entregado el 07/09/2026',

    /*
    | Membresias. El precio del documento ($118, $360, $950) es con IGV; en la base
    | se guardan por separado el precio y el porcentaje, asi que aqui va la base.
    */
    'membresias' => [
        'School' => [
            'price'                 => 100.00,  // 118 con IGV
            'fast_cash_bonus'       => 15.00,   // Bono de Efectivo Rapido
            'course_selling_bonus'  => 15.00,   // Bono Promueve y Vende
            'disc_purchases_course' => 15.00,   // Bono de Descuentos en Cursos
            'pay_in_binary'         => 15.00,   // Bono Binario sobre la pierna de pago
            'productor_bonus'       => 30.00,   // Bono de Propiedad Intelectual
            'puntos_afiliacion'     => 29,
            'enrollment_duration'   => 12,
        ],
        'Academy' => [
            'price'                 => 305.00,  // 360 con IGV
            'fast_cash_bonus'       => 20.00,
            'course_selling_bonus'  => 20.00,
            'disc_purchases_course' => 20.00,
            'pay_in_binary'         => 20.00,
            'productor_bonus'       => 30.00,
            'puntos_afiliacion'     => 89,
            'enrollment_duration'   => 12,
        ],
        'University' => [
            'price'                 => 805.00,  // 950 con IGV
            'fast_cash_bonus'       => 25.00,
            'course_selling_bonus'  => 25.00,
            'disc_purchases_course' => 25.00,
            'pay_in_binary'         => 25.00,
            'productor_bonus'       => 30.00,
            'puntos_afiliacion'     => 234,
            'enrollment_duration'   => 12,
        ],
    ],

    /*
    | OPC. "Su valor es de $60 USD (...) genera 15 puntos mensuales". El documento no
    | distingue por membresia: es el mismo para todos.
    */
    'opc' => [
        'price'  => 60.00,
        'points' => 15,
    ],

    /*
    | Corte binario. "Se realiza todos los dias 21 de cada mes a las 12:00 PM
    | (hora Lima, Peru)".
    */
    'corte' => [
        'frequency' => 'monthly',
        'day'       => 21,
        'time'      => '12:00',
        'timezone'  => 'America/Lima',
    ],

    /*
    | Rangos, en el orden del documento.
    |
    |   vol_min          puntos de la pierna mas pequena al final del mes
    |   active_direct    miembros directos activos
    |   pack_max         miembros con membresia University exigidos
    |   max_pay          tope de cobro
    |   monthly_bonus    bonificacion por mantener el rango
    |   limit_generation generaciones que desbloquea el bono generacional
    */
    'rangos' => [
        ['name' => 'Mentor',                  'vol_min' => 70,      'active_direct' => 2,  'pack_max' => 0,    'max_pay' => 500,    'monthly_bonus' => 0,     'monthly_bonus_months' => 3, 'monthly_bonus_frequency' => 'monthly',   'limit_generation' => 1],
        ['name' => 'Entrenador Académico',    'vol_min' => 840,     'active_direct' => 2,  'pack_max' => 1,    'max_pay' => 1000,   'monthly_bonus' => 0,     'monthly_bonus_months' => 3, 'monthly_bonus_frequency' => 'monthly',   'limit_generation' => 2],
        ['name' => 'Coach Acreditado',        'vol_min' => 2800,    'active_direct' => 3,  'pack_max' => 2,    'max_pay' => 2000,   'monthly_bonus' => 0,     'monthly_bonus_months' => 3, 'monthly_bonus_frequency' => 'monthly',   'limit_generation' => 3],
        ['name' => 'Master Acreditado',       'vol_min' => 9800,    'active_direct' => 7,  'pack_max' => 5,    'max_pay' => 5000,   'monthly_bonus' => 0,     'monthly_bonus_months' => 3, 'monthly_bonus_frequency' => 'monthly',   'limit_generation' => 4],
        ['name' => 'Sub Director',            'vol_min' => 20650,   'active_direct' => 9,  'pack_max' => 12,   'max_pay' => 10000,  'monthly_bonus' => 0,     'monthly_bonus_months' => 3, 'monthly_bonus_frequency' => 'monthly',   'limit_generation' => 5],
        ['name' => 'Director',                'vol_min' => 63000,   'active_direct' => 12, 'pack_max' => 30,   'max_pay' => 30000,  'monthly_bonus' => 2000,  'monthly_bonus_months' => 3, 'monthly_bonus_frequency' => 'monthly',   'limit_generation' => 6],
        ['name' => 'Decano',                  'vol_min' => 126000,  'active_direct' => 15, 'pack_max' => 80,   'max_pay' => 50000,  'monthly_bonus' => 4000,  'monthly_bonus_months' => 3, 'monthly_bonus_frequency' => 'monthly',   'limit_generation' => 7],
        ['name' => 'Vice Rector',             'vol_min' => 259000,  'active_direct' => 18, 'pack_max' => 150,  'max_pay' => 75000,  'monthly_bonus' => 7000,  'monthly_bonus_months' => 3, 'monthly_bonus_frequency' => 'monthly',   'limit_generation' => 8],
        ['name' => 'Rector',                  'vol_min' => 777000,  'active_direct' => 25, 'pack_max' => 420,  'max_pay' => 80000,  'monthly_bonus' => 10000, 'monthly_bonus_months' => 3, 'monthly_bonus_frequency' => 'monthly',   'limit_generation' => 8],
        ['name' => 'Rector Presidente',       'vol_min' => 880000,  'active_direct' => 30, 'pack_max' => 980,  'max_pay' => 100000, 'monthly_bonus' => 15000, 'monthly_bonus_months' => 3, 'monthly_bonus_frequency' => 'monthly',   'limit_generation' => 8],
        ['name' => 'Rector Presidente Crown', 'vol_min' => 1000000, 'active_direct' => 40, 'pack_max' => 1500, 'max_pay' => 200000, 'monthly_bonus' => 15000, 'monthly_bonus_months' => 3, 'monthly_bonus_frequency' => 'quarterly', 'limit_generation' => 8],
    ],

    /*
    | Rangos que existen en el sistema pero no en el documento y que no son un error.
    | "Aprendiz" es el rango de partida: el que se asigna a quien todavia no llega a
    | Mentor. El documento no lo nombra porque no da derecho a nada.
    */
    'rangos_fuera_del_documento' => ['Aprendiz'],

    /*
    | Bono generacional. La tabla del documento repite los mismos porcentajes en
    | todos los rangos y lo que cambia es hasta que generacion llegan, que es el
    | limit_generation de cada rango.
    */
    'generacional' => [
        'porcentajes' => [1 => 5, 2 => 5, 3 => 5, 4 => 3, 5 => 2, 6 => 1, 7 => 1, 8 => 1],
        // "A partir de la tercera generacion en adelante, es necesario contar con la
        // membresia University para recibir estas comisiones."
        'university_desde' => 3,
    ],

    /*
    | Bonos del documento que hoy no tiene el sistema, ni siquiera una tabla donde
    | apuntarlos. Se listan para que el informe los saque y no se den por hechos.
    */
    'bonos_sin_implementar' => [
        'Bono Viaje'             => 'Dos veces al año (abril y octubre) más uno exclusivo para Directores. No hay clasificación ni registro de participantes.',
        'Bono Vehículo Familiar' => '$25,000 al mantener Director 3 meses seguidos. No hay proceso que lo detecte ni lo registre.',
        'Bono Vehículo de Lujo'  => 'Hasta $80,000 al mantener Rector Presidente Crown 3 meses seguidos. Igual que el anterior.',
    ],

    /*
    | Al reves: lo que el sistema paga y el documento no menciona.
    */
    'fuera_del_documento' => [
        'Bono de expansión' => 'El monolito lo pagaba y se portó al sistema nuevo como comando mensual. El documento entregado no lo recoge en ninguno de sus diez bonos.',
    ],
];
