
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DICE: Email Campaign</title>
    <style>
    /* General resets for maximum compatibility */
    body,
    table,
    td,
    a {
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
    }

    table,
    td {
        mso-table-lspace: 0pt;
        mso-table-rspace: 0pt;
    }

    img {
        -ms-interpolation-mode: bicubic;
        border: 0;
        outline: none;
        text-decoration: none;
        display: block;
    }

    table {
        border-collapse: collapse !important;
    }

    body {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background-color: #ffffff;
    }

    a {
        text-decoration: none;
    }

    .desktop-hide {
        display: none !important;
    }

    .mobile-hide {
        display: block !important;
    }

    /* Responsive styles */
    @media screen and (max-width: 600px) {
        .responsive-table {
            width: 100% !important;
        }

        .step-container td.column,
        .offer-container td.column {
            display: block;
            width: 100% !important;
        }

        .step-container td.column p.lastcol {
            margin-top: 2px !important;
        }

        .mobile-hide {
            display: none !important;
        }

        .desktop-hide {
            display: block !important;
        }

        .mobile-center {
            text-align: center !important;
        }

        .mobile-padding {
            padding: 20px 10px !important;
        }

        .footer-icons td {
            display: inline-block !important;
            text-align: center !important;
            margin-right: 10px !important;
        }

        .column {
            display: inline-block !important;
            width: 46% !important;
            vertical-align: top !important;
            margin-bottom: 10px !important;
        }

        .column:nth-child(2n) {
            margin-right: 0 !important;
        }

        .stack-column {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
        }

    }
    </style>
</head>

<body style="margin: 0; padding: 0; background-color: #ffffff;">
    <!-- Main Wrapper Table -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #ffffff;">
        <tr>
            <td align="center" style="padding: 0px;">
                <!-- Email Content Table -->
                <table border="0" cellpadding="0" cellspacing="0" width="650" class="responsive-table" style="max-width: 650px; background-color: #ffffff;">
                    <!-- Top header -->
                    <!-- Top Header Logos -->
                    <tr>
                        <td align="center" style="padding:  0; background-color: #ffffff;">
                            <table border="0" cellpadding="0" cellspacing="0" width="400px">
                                <tr>
                                    <td width="10%" style="padding: 2px;"></td>
                                    <!-- First Logo -->
                                    <td align="center" width="35%">
                                        <a href="https://www.auxilo.com/" target="_blank" style="display:block; width: 100%;"><img src="https://www.auxilo.com/emailers/dice-campaign/auxilo-logo.jpg" alt="Logo 1" width="100%" height="auto" style="display: block; max-width: 178px;"></a>
                                    </td>
                                    <!-- Divider Line -->
                                    <td align="center" width="10%">
                                        <div style="width: 1px; height: 30px; background-color: #933115;"></div>
                                    </td>
                                    <!-- Second Logo -->
                                    <td align="center" width="35%">
                                        <a href="javascript:void(0)" target="_blank" style="display:block; width: 100%;"><img src="https://www.auxilo.com/emailers/dice-campaign/Dice-logo.jpg" alt="Logo 2" width="100%" height="auto" style="display: block; max-width: 178px;"></a>
                                    </td>
                                    <td width="10%" style="padding: 2px;"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Logo Section Ends-->
                    <!-- Banner Section Starts -->
                    <tr>
                        <td align="center" style="padding: 0px; background-color: #ffffff;">
                            <img src="https://www.auxilo.com/emailers/dice-campaign/Dice-email-campaign-header.jpg" width="100%" alt="Identify Fake Loan Offers" style="max-width: 650px; height: auto; display: block; outline: none;"></td>
                    </tr>
                    <!-- Banner Section Ends -->
                    <!-- Body starts Starts -->
                    <tr>
                        <td align="center" style="padding: 20px; padding-top: 0px;padding-bottom: 0; background-color: #ffffff;">
                            <p style="margin:5px 0; margin-top:0px; padding:0; font-family: Arial, sans-serif; font-size: 18px; font-weight: normal; line-height: 22px; color: #000000; text-align: center;">Hi <span style="margin:5px 0; margin-top:0px; padding:0; font-family: Arial, sans-serif; font-size: 20px; font-weight: bold; color: #000000;">{{ $name }},</span></p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 40px; padding-top: 0px;padding-bottom: 10px; background-color: #ffffff; text-align: center; width: 100%;">
                            <p style="font-size:18px; color:#000000; margin:5px 0; font-family:Arial, sans-serif; line-height:20px; text-align: center;">
                                Please find below the execution summary of the
                            </p>
                            <p style="font-size:18px; color:#000000; margin:5px 0; font-family:Arial, sans-serif; line-height:20px; text-align: center;">
                                <span style="margin:5px 0; margin-top:0px; padding:0; font-family: Arial, sans-serif; font-size: 20px; font-weight: bold; color: #1d234c;">{{$campaign_name}}</span> email campaign sent on <span style="margin:5px 0; margin-top:0px; padding:0; font-family: Arial, sans-serif; font-size: 20px; font-weight: bold; color: #1d234c;">{{$campaign_date}}</span>
                        </td>
                    </tr>
                    <!-- Campaign Performance Overview Starts -->
                    <tr>
                        <td align="center" style="padding: 10px; padding-top: 20px;padding-bottom: 20px; background-color: #fef0e8; text-align: center; width: 100%;">
                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                                <tr>
                                    <td align="center" style="padding: 0px; padding-top: 0px;padding-bottom: 10px; text-align: center; width: 100%;">
                                        <p style="margin:5px 0; margin-top:0px; padding:0; font-family: Arial, sans-serif; font-size: 20px; font-weight: bold; color: #000000;">Campaign Performance Overview</p>
                                    </td>
                                </tr>
                            </table>
                            <!-- Data columns starts -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <!-- Column 1 -->
                                    <td class="column" width="120" style="padding-right:10px; vertical-align: top;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #1d234c;">
                                            <tr>
                                                <td style="padding: 30px; padding-bottom: 0px; padding-top: 10px;">
                                                    <img src="https://www.auxilo.com/emailers/dice-campaign/email-sent-icon.png" alt="Column 1" style="width:100%; display:block;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <hr style="border:0; border-top:1px dashed #ffffff;margin: 6px;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 10px;">
                                                    <p style="font-size:14px; font-weight: normal; color:#ffffff; margin:0; font-family:Arial, sans-serif; line-height:22px; text-align: center;">
                                                        Emails Sent<br>
                    
                    <span style="font-size:20px; font-weight: bold; color:#ffffff;">
                        {{isset($result['processed']) && $result['processed'] != '' ? $result['processed'] : 0}}
                    </span>
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <!-- Column 2 -->
                                    <td class="column" width="120" style="padding-right:10px; vertical-align: top;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f37021;">
                                            <tr>
                                                <td style="padding: 30px; padding-bottom: 0px; padding-top: 10px;">
                                                    <img src="https://www.auxilo.com/emailers/dice-campaign/email-delivered-icon.png" alt="Column 1" style="width:100%; display:block;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <hr style="border:0; border-top:1px dashed #ffffff;margin: 6px;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 10px;">
                                                    <p style="font-size:14px; font-weight: normal; color:#ffffff; margin:0; font-family:Arial, sans-serif; line-height:22px; text-align: center;">
                                                        Delivered<br>
                    
                                                        <span style="font-size:20px; font-weight: bold; color:#ffffff;">{{isset($result['delivered']) && $result['delivered'] != '' ? $result['delivered'] : 0}}</span>
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <!-- Column 3 -->
                                    <td class="column" width="120" style="padding-right:10px; vertical-align: top;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #434c5e;">
                                            <tr>
                                                <td style="padding: 30px; padding-bottom: 0px; padding-top: 10px;">
                                                    <img src="https://www.auxilo.com/emailers/dice-campaign/email-opened-icon.png" alt="Column 1" style="width:100%; display:block;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <hr style="border:0; border-top:1px dashed #ffffff;margin: 6px;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 10px;">
                                                    <p style="font-size:14px; font-weight: normal; color:#ffffff; margin:0; font-family:Arial, sans-serif; line-height:22px; text-align: center;">
                                                        Opened<br>
                                                        <span style="font-size:20px; font-weight: bold; color:#ffffff;">

                    {{isset($result['open']) && $result['open'] != '' ? $result['open'] : 0}}

                                                        </span>
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <!-- Column 4 -->
                                    <td class="column" width="120" style="padding-right:10px; vertical-align: top;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #c0841a;">
                                            <tr>
                                                <td style="padding: 30px; padding-bottom: 0px; padding-top: 10px;">
                                                    <img src="https://www.auxilo.com/emailers/dice-campaign/email-clicked-icon.png" alt="Column 1" style="width:100%; display:block;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <hr style="border:0; border-top:1px dashed #ffffff;margin: 6px;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 10px;">
                                                    <p style="font-size:14px; font-weight: normal; color:#ffffff; margin:0; font-family:Arial, sans-serif; line-height:22px; text-align: center;">
                                                        Clicked<br>
                                                        <span style="font-size:20px; font-weight: bold; color:#ffffff;">
                    {{isset($result['click']) && $result['click'] != '' ? $result['click'] : 0}}

                                                        </span>
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <!-- Column 5 -->
                                </tr>
                            </table>
                            <!-- Data columns ends -->
                            <!-- Email id health section starts -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 20px;">
                                <tr>
                                    <!-- Left: Image (40%) -->
                                    <td class="stack-column" width="40%" valign="top" style="padding:0; margin:0; padding-right: 10px; vertical-align: bottom;">
                                        <!-- Transparent spacer to match height -->
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td valign="top" style="padding:0; margin:0;">
                                                    <img src="https://www.auxilo.com/emailers/dice-campaign/dice-bottom-graph.png" alt="Left Side" width="100%" style="display:block; height:auto;">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <!-- Right: Content table (60%) -->
                                    <td class="stack-column" width="60%" valign="top" style="padding:0;padding-bottom: 10px; background-color:#1d234c;">
                                        <table role="presentation" width="100%" cellpadding="20" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding: 0; padding-top: 10px; width: 100%;" colspan="2">
                                                    <p style="margin:5px 0; margin-top:0px; padding:0; font-family: Arial, sans-serif; font-size: 20px; font-weight: bold; color: #ffffff; text-align: center; width: 100%;">Email ID Health</p>
                                                    <hr style="border:0; border-top:1px solid #ffffff; ">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 0; width: 50%;">
                                                    <p style="font-size:14px; font-weight: normal; color:#ffffff; margin:0; font-family:Arial, sans-serif; line-height:22px; text-align: center;">
                                                        Valid Email IDs<br>
                                                        <span style="font-size:20px; font-weight: bold; color:#ffffff;">

                    {{isset($emailDump['clean']) && $emailDump['clean'] != '' ? $emailDump['clean'] : 0}}

                                                        </span>
                                                    </p>
                                                </td>
                                                <td style="padding: 0; padding-bottom: 5px; border-left: 1px solid #ffffff; width: 50%;">
                                                    <p style="font-size:14px; font-weight: normal; color:#ffffff; margin:0; font-family:Arial, sans-serif; line-height:22px; text-align: center;">
                                                        Invalid Email IDs<br>
                                                        <span style="font-size:20px; font-weight: bold; color:#ffffff;">
                    {{isset($emailDump['dirty']) && $emailDump['dirty'] != '' ? $emailDump['dirty'] : 0}}

                                                        </span>
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <!-- Email id health section ends -->
                        </td>
                    </tr>
                    <!-- Footer Section -->
                    <tr>
                        <td align="center" style="padding:5px; background-color: #1d234c;">
                           <p style="font-size:9px; font-weight: normal; color:#ffffff; margin:0; font-family:Arial, sans-serif; line-height:10px; text-align: center;">Generated via D.I.C.E (Data Integrated Communication Engine)</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>