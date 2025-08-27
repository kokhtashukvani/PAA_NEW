<?php
// /api/routes/ping.php

// This is a simple health-check endpoint.
// If the router is working, a GET request to /api/ping will execute this file.

json_response(['message' => 'pong'], 200);
?>
