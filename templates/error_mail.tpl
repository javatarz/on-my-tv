--{$boundary}
Content-Type: text/plain;charset="utf-8"

------------------------------
DATA
------------------------------
User ID:    {$u_id}
User Name:  {$u_name}

File:       {$e_file}
Line:       {$e_line}
Code:       {$e_code}
Message:    {$e_msg}


------------------------------
TRACE
------------------------------
{$e_trace}


------------------------------
INPUT POST
------------------------------
{$i_post}


------------------------------
INPUT GET
------------------------------
{$i_get}


------------------------------
INPUT SERVER
------------------------------
{$i_server}


------------------------------
INPUT COOKIE
------------------------------
{$i_cookie}

--{$boundary}
Content-Type: text/html;charset="utf-8"

<h3>DATA</h3>    
User ID:    {$u_id}<br />
User Name:  {$u_name}<br />
<br />
File:       {$e_file}<br />
Line:       {$e_line}<br />
Code:       {$e_code}<br />
Message:    {$e_msg}<br />
<br />
<h3>TRACE</h3>
<pre style="border: 1px solid black;">{$e_trace}</pre>

<h3>POST INPUT</h3>
<pre style="border: 1px solid black;">{$i_post}</pre>

<h3>GET INPUT</h3>
<pre style="border: 1px solid black;">{$i_get}</pre>

<h3>SERVER INPUT</h3>
<pre style="border: 1px solid black;">{$i_server}</pre>

<h3>COOKIE INPUT</h3>
<pre style="border: 1px solid black;">{$i_cookie}</pre>

--{$boundary}--