# Buscar Empresas en SII

Es una pequeña clase, que mediante el rut, retorna los datos publicos que impuestos internos entrega, como la razón social, rut, fecha de inicio de actividades, y los giros que la empresa tiene.

## Modo de uso 
```PHP
    $sii = new Sii();
    
    $empresa = $sii->buscarEmpresaSII('96714870-9');
```

Esto retornara un arreglo con los siguientes valores
```
Array
(
    [execution] => 1
    [http_code] => 200
    [result] => Array
        (
            [arr_datosEmpresa] => Array
                (
                    [gl_razonSocial] => Coca Cola De Chile S A
                    [gl_rut] => 96714870-9
                    [bo_proPyme] => 0
                    [fc_inicioActividad] => 10-11-1994
                )

            [arr_actividades] => Array
                (
                    [0] => Array
                        (
                            [gl_giro] => ELABORACION DE OTROS PRODUCTOS ALIMENTICIOS N.C.P.
                            [gl_codigo] => 107909
                            [gl_categoria] => Primera
                            [gl_afecta] => Si
                        )

                    [1] => Array
                        (
                            [gl_giro] => ELABORACION DE BEBIDAS NO ALCOHOLICAS
                            [gl_codigo] => 110401
                            [gl_categoria] => Primera
                            [gl_afecta] => Si
                        )

                    [2] => Array
                        (
                            [gl_giro] => VENTA AL POR MAYOR NO ESPECIALIZADA
                            [gl_codigo] => 469000
                            [gl_categoria] => Primera
                            [gl_afecta] => Si
                        )

                    [3] => Array
                        (
                            [gl_giro] => SERVICIOS DE PUBLICIDAD PRESTADOS POR EMPRESAS
                            [gl_codigo] => 731001
                            [gl_categoria] => Primera
                            [gl_afecta] => Si
                        )

                    [4] => Array
                        (
                            [gl_giro] => ESTUDIOS DE MERCADO Y ENCUESTAS DE OPINION PUBLICA
                            [gl_codigo] => 732000
                            [gl_categoria] => Primera
                            [gl_afecta] => Si
                        )

                    [5] => Array
                        (
                            [gl_giro] => OTRAS ACTIVIDADES DE SERVICIOS DE APOYO A LAS EMPRESAS N.C.P.
                            [gl_codigo] => 829900
                            [gl_categoria] => Primera
                            [gl_afecta] => No
                        )

                )

        )

)
```
