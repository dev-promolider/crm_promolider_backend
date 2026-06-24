<?php
/**
 * EMAIL HEADER TEMPLATE
 * Uso: include 'email/layouts/header.php';
 * 
 * Variables requeridas:
 * - $titulo_principal (string): Título principal del email
 * - $subtitulo (string): Subtítulo descriptivo
 */

// Valores por defecto si no se proporcionan
$titulo_principal = $titulo_principal ?? '¡Inscripción Confirmada!';
$subtitulo = $subtitulo ?? 'Tu lugar en la masterclass está asegurado';
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="x-apple-disable-message-reformatting" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title><?php echo htmlspecialchars($titulo_principal); ?> - encabezado</title>

    <style type="text/css">
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        @media only screen and (min-width: 620px) {
            .u-row {
                width: 600px !important;
            }
        }

        @media (max-width: 620px) {
            .u-row-container {
                max-width: 100% !important;
                padding-left: 0px !important;
                padding-right: 0px !important;
            }
            .u-row .u-col {
                min-width: 320px !important;
                max-width: 100% !important;
                display: block !important;
            }
            .u-row {
                width: calc(100% - 40px) !important;
            }
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        table, tr, td {
            vertical-align: top;
            border-collapse: collapse;
        }

        p {
            margin: 0;
        }
    </style>
</head>

<body style="margin: 0; padding: 0; -webkit-text-size-adjust: 100%; background-color: #f8f9fa; color: #2c3e50;">
    <table style="border-collapse: collapse; table-layout: fixed; border-spacing: 0; vertical-align: top; min-width: 320px; margin: 0 auto; background-color: #f8f9fa; width: 100%;" cellpadding="0" cellspacing="0">
        <tbody>
            <tr style="vertical-align: top">
                <td style="word-break: break-word; border-collapse: collapse !important; vertical-align: top;">
                    
                    <!-- Header -->
                    <div class="u-row-container" style="padding: 0px; background-color: transparent">
                        <div class="u-row" style="
                  margin: 0 auto;
                  min-width: 320px;
                  max-width: 600px;
                  overflow-wrap: break-word;
                  word-wrap: break-word;
                  word-break: break-word;
                  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
                ">
                            <div style="
                    border-collapse: collapse;
                    display: table;
                    width: 100%;
                    background-color: transparent;
                  ">
                                <div class="u-col u-col-100" style="
                      max-width: 320px;
                      min-width: 600px;
                      display: table-cell;
                      vertical-align: top;
                    ">
                                    <div style="
                        width: 100% !important;
                        border-radius: 0px;
                        -webkit-border-radius: 0px;
                        -moz-border-radius: 0px;
                      ">
                                        <div style="
                          padding: 0px;
                          border-top: 0px solid transparent;
                          border-left: 0px solid transparent;
                          border-right: 0px solid transparent;
                          border-bottom: 0px solid transparent;
                          border-radius: 0px;
                          -webkit-border-radius: 0px;
                          -moz-border-radius: 0px;
                        ">
                                            <table style="font-family: 'Inter', sans-serif" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                <tbody>
                                                    <tr>
                                                        <td style="
                                  overflow-wrap: break-word;
                                  word-break: break-word;
                                  padding: 0px;
                                  font-family: 'Inter', sans-serif;
                                " align="center">
                                                            <img src="https://promolider-storage-user.s3-accelerate.amazonaws.com/images/promolider_logo.png" alt="Promolíder" style="
                                                              outline: none;
                                                              text-decoration: none;
                                                              -ms-interpolation-mode: bicubic;
                                                              clear: both;
                                                              display: inline-block !important;
                                                              border: none;
                                                              height: auto;
                                                              float: none;
                                                              width: 180px;
                                                              max-width: 180px;
                                                            " width="180" />
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="
                                  overflow-wrap: break-word;
                                  word-break: break-word;
                                  padding: 20px 0 0;
                                  font-family: 'Inter', sans-serif;
                                " align="center">
                                                            <h1 style="
                                                                        margin: 0;
                                                                        color: #ffffff;
                                                                        line-height: 1.2;
                                                                        text-align: center;
                                                                        font-weight: 300;
                                                                        font-family: 'Inter', sans-serif;
                                                                        font-size: 28px;
                                                                        letter-spacing: -0.5px;
                                                                    ">
                                                                <?php echo htmlspecialchars($titulo_principal); ?>
                                                            </h1>
                                                            <p style="
                                                                        margin: 8px 0 0;
                                                                        color: #bdc3c7;
                                                                        font-size: 16px;
                                                                        font-weight: 400;
                                                                        text-align: center;
                                                                    ">
                                                                <?php echo htmlspecialchars($subtitulo); ?>
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>