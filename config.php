<?php
define('PRIVATE_MODE', getenv('PRIVATE_MODE') ?: 'edit');

define('USER_ID', getenv('USER_ID') ?: '6678ec58-e7d9-46e7-ae25-b92127ef056e');

define('USERNAME', getenv('USERNAME') ?: '');

define('PASSWORD', getenv('PASSWORD') ?: '');

define('JWT_KEY', getenv('JWT_KEY') ?: 'cc63eb9d-cde1-442b-b7b1-5d483f6bf511');

define('HOSTED_ON', getenv('HOSTED_ON') ?: '');

define('HOSTED_ON_URL', getenv('HOSTED_ON_URL') ?: '');

define('NOTES_MAX_SIZE', getenv('NOTES_MAX_SIZE') ?: 1024 * 1024 * 1024);

define('NOTE_MAX_LENGTH', getenv('NOTE_MAX_LENGTH') ?: 24 * 1024);
