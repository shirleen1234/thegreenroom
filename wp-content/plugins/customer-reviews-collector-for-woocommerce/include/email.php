<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<!-- NAME: SOFT -->
<!--[if gte mso 15]>
<xml>
<o:OfficeDocumentSettings>
<o:AllowPNG/>
<o:PixelsPerInch>96</o:PixelsPerInch>
</o:OfficeDocumentSettings>
</xml>
<![endif]-->
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="supported-color-schemes" content="light dark">
<title>Rate us on Google</title>
<style type="text/css">

#outlook a {
padding: 0;
}

.ReadMsgBody {
width: 100%;
}
.ExternalClass {
width: 100%;
}

.ExternalClass,
.ExternalClass p,
.ExternalClass span,
.ExternalClass font,
.ExternalClass td,
.ExternalClass div {
line-height: 100%;
}

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
}


body {
margin: 0;
padding: 0;
}
img {
border: 0;
height: auto;
line-height: 100%;
outline: none;
text-decoration: none;
}
table {
border-collapse: collapse !important;
}
body {
height: 100% !important;
margin: 0;
padding: 0;
width: 100% !important;
}

.appleBody a {
color: #68440a;
text-decoration: none;
}
.appleFooter a {
color: #999999;
text-decoration: none;
}
.content-table, .header { background-color: #ffffff }
.content-table { border-radius: 8px }

@media screen and (max-width: 525px) {
table[class="wrapper"],table[class="content-table"] , table[class="footer"]{
width: 100% !important;
}
}
</style>
</head>
<body style="margin:0; padding:0;">
<table border="0" class="main" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed">
<tr>
<td align="center" bgcolor="#eff0f4" style="padding-bottom: 30px;">
<?php if (isset($logoImage) && $logoImage && $logoImage !== 'delete'): ?>
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="header">
<tr>
<td style="padding: 20px 0px 20px 0px" align="center">
<img alt="" src="<?php echo $logoImage; ?>" width="150" style="width: 150px; display: block" border="0" />
</td>
</tr>
</table>
<?php endif; ?>
<table border="0" cellpadding="0" cellspacing="0" width="550" class="content-table" style="margin: 30px 15px; margin-bottom: 0">
<tr>
<td align="left" id="email-content" style="padding: 30px; font-size: 18px; font-family: Arial, sans-serif; color: #000000; text-decoration: none; line-height: 25px;">
<?php echo $tiEmailContent; ?>
</td>
</tr>
</table>
<?php if (isset($tiEmailFooterContent) && $tiEmailFooterContent): ?>
<table border="0" cellpadding="0" cellspacing="0" width="550" class="footer">
<tr>
<td align="center" style="padding: 0; padding-top: 30px; font-size: 13px; font-family: Arial, sans-serif; color: #9da1a3; text-decoration: none; line-height: 20px;" id="email-footer-content">
<?php echo $tiEmailFooterContent; ?>
</td>
</tr>
</table>
<?php endif; ?>
</td>
</tr>
</table>
</body>
</html>