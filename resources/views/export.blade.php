<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Data - {{ $user->name }}</title>
    <style>
        :root {
            --bg-color: #f3f4f6;
            --container-bg: #ffffff;
            --text-main: #111827;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            line-height: 1.5;
            padding: 2rem;
            margin: 0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 2rem;
            margin: 0;
            color: var(--text-main);
        }

        .header p {
            color: var(--text-muted);
            margin-top: 0.5rem;
        }

        .section {
            background: var(--container-bg);
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .section h2 {
            font-size: 1.25rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.75rem;
            margin-top: 0;
            margin-bottom: 1.5rem;
            color: var(--text-main);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        table:last-child {
            margin-bottom: 0;
        }

        th, td {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            text-align: left;
            word-break: break-word;
        }

        th {
            background-color: #f9fafb;
            font-weight: 600;
            width: 30%;
            color: var(--text-muted);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        td {
            color: var(--text-main);
        }

        .item-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .item-card {
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            overflow: hidden;
        }

        .item-card table {
            margin: 0;
            border: none;
        }

        .item-card th, .item-card td {
            border: none;
            border-bottom: 1px solid var(--border-color);
        }

        .item-card tr:last-child th, .item-card tr:last-child td {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Exported Data</h1>
            <p>Generated for {{ $user->name }} on {{ now()->format('Y-m-d H:i:s') }}</p>
        </div>

        <div class="section">
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
        </div>

        <div class="section">
            <h2>Server Details</h2>
            <div class="item-list">
                @forelse($user->servers as $server)
                    <div class="item-card">
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
                    </div>
                @empty
                    <p style="text-align: center; color: var(--text-muted); padding: 1rem 0;">No server details found.</p>
                @endforelse
            </div>
        </div>

        <div class="section">
            <h2>Message Details</h2>
            <div class="item-list">
                @forelse($user->messages as $message)
                    <div class="item-card">
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
                    </div>
                @empty
                    <p style="text-align: center; color: var(--text-muted); padding: 1rem 0;">No message details found.</p>
                @endforelse
            </div>
        </div>
    </div>
</body>
</html>