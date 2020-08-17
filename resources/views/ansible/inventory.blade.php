[autopilot]
@foreach($servers as $server)
{!! $server->name !!} ansible_user={!! $server->user !!} ansible_host={!! $server->address !!} ansible_port={!! $server->port !!} ansible_port={!! $server->port !!} ansible_ssh_private_key_file={!! $server->keyPath() !!} ansible_python_interpreter=/usr/bin/python3
@endforeach