<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>{{$title}}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body style="margin: 0; padding: 0;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td>
                <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse;">
                    <tr>
                        <td>
                            <table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
                                <tr>
                                    <td align="center" style="text-align: center;padding:20px 0px;"><a href="https://bargains.com.au/" target="_blank"><img src="https://bargains.com.au/img/bargains-logo.png" alt="Bargains.com.au" style="display: block;" /></a></td>
                                </tr>
                                <tr>
                                    <td bgcolor="#ffffff" style="color: #000000; font-family: Arial, sans-serif; font-size: 24px;">
                                        <b>{{$title}}</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td bgcolor="#ffffff" style="color: #153643; font-family: Arial, sans-serif; font-size: 12px; line-height: 20px;">
                                        @yield('content')
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
