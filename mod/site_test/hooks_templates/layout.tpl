<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{$title|escape}</title>
    <link rel="shortcut icon" href="{$favicon}" type="image/x-icon" />
  </head>
  <body>
    {hook mod='site_test' name='banner'}
    {hook mod='site_test' name='content'}
    {hook mod='site_test' name='footer'}
  </body>
</html>
