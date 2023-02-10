<!DOCTYPE html>
<html>
    <head>
     <title>{{ $details['subject'] }}</title>
    </head>
    <body>
     <p>Hi there,</p>
     <p>Good day!</p>
     <p>{{ ucfirst($details['message']) }}</p><br/>
     <p>Kind regards,</p>
     <p>{{ ucwords($details['name']) }}</p>
     <br/>
     <p>Reply to: {{ $details['email'] }}</p>
    </body>
</html> 