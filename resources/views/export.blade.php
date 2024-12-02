<!DOCTYPE html>
<html>
<head>
    <title>User Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
    </style>
</head>
<body>
<style>
    td {
        max-width: 600px; /* Set a maximum width */
        word-wrap: break-word; /* Ensure long words break */
    }
</style>
<h2>User Details</h2>
<table>
    <tr>
        <th>Name</th>
        <td>{{ $user->name }}</td>
    </tr>
    <tr>
        <th>Icon url</th>
        <td>{{ empty($user->icon) ? 'None' : substr(asset(''), 0, -1).$user->icon }}</td>
    </tr>
    <tr>
        <th>Email</th>
        <td>{{ $user->email }}</td>
    </tr>
    <tr>
        <th>Created at</th>
        <td>{{ $user->created_at }}</td>
    </tr>
</table>
<h2>Server Details</h2>
@foreach($user->servers as $server)
    <table>
        <tr>
            <th>Name</th>
            <td>{{ $server->name }}</td>
        </tr>
        <tr>
            <th>Icon url</th>
            <td>{{ empty($server->icon) ? 'None' : substr(asset(''), 0, -1).$server->icon }}</td>
        </tr>
        <tr>
            <th>Description</th>
            <td>{{ empty($server->description) ? 'None' : $server->description }}</td>
        </tr>
        <tr>
            <th>Created at</th>
            <td>{{ $server->created_at }}</td>
        </tr>
    </table>
    <br/>
@endforeach
<h2>Message Details</h2>
@foreach($user->messages as $message)
    <table>
        <tr>
            <th>In server:</th>
            <td>{{ $message->channel->server->name }}</td>
        </tr>
        <tr>
            <th>Type</th>
            <td>{{ $message->type }}</td>
        </tr>
        <tr>
            <th>Data</th>
            <td>{{ $message->type == \App\Enums\MessageType::Image->value ? (empty($message->mdata) ? 'None' : substr(asset(''), 0, -1).$message->icon) : $message->mdata }}</td>
        </tr>
        <tr>
            <th>Created at</th>
            <td>{{ $message->created_at }}</td>
        </tr>
    </table>
    <br/>
@endforeach
</body>
</html>
