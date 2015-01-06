INSERT IGNORE INTO `sys_postmaster_template` (`postmaster_template_id`, `section`, `area`, `action`, `subject`, `body`, `priority`, `secondary_emails`, `reply_to`, `enabled`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'MSHIP', 'ACCOUNT', 'CREATED', 'Membership Account Created - CID {{{ $recipient->account_id }}}', 'Dear {{{ $recipient->name }}},\n\nThis email serves as confirmation that your central account with VATSIM UK has been created.  We have received the following details about you:\n\nCID: {{{ $recipient->account_id }}}\nFull Name: {{{ $recipient->name }}}\nPrimary Email: {{{ $recipient->primary_email->email }}}\nSecondary Emails:\n@foreach($recipient->secondary_email as $e)\n-- {{{ $e->email }}}\n@endforeach\n\n@if(count($recipient->secondary_email) < 1)\n No secondary emails registered.\n@endif\nStatus: {{{ $recipient->status }}}\nState: {{{ $recipient->primary_state }}}\n\nATC Qualification: {{{ $recipient->qualification_atc }}}\nPilot Qualification(s): {{{ $recipient->qualifications_pilot_string }}}\n\nNow that your account has been created, you can login to any of our web services and these details will be transferred automatically.\n\nFurthermore, any updates you make to your details via the vatsim.net membership portal will be synchronised with our database in a timely manner, to ensure all our systems remain up to date.\n\nIf any details are incorrect, or you have any concerns, please contact {{ link_to(''mailto:community@vatsim-uk.co.uk'', ''our community department'') }} who will be able to help you further.\n', 50, 1, 'community@vatsim-uk.co.uk', 1, '2015-01-03 17:00:20', '0000-00-00 00:00:00', NULL),
(2, 'MSHIP', 'SECURITY', 'FORGOTTEN', 'SSO Secondary Password Reset', 'Dear {{{ $recipient->name }}},\r\n\r\nYou, or somebody posing as you, has advised us that a new secondary password should be generated for your account.\r\n\r\nIn order to verify this request, you need to click the link in this email.  This request will expire in 12 hours.\r\n\r\nIP: {{{ $data->ip }}}\r\n\r\nTo authorise this request, please {{ link_to(url("/mship/security/forgotten-link", ["code" => $data->token->code]), "click here") }}.  Alternatively, copy the link below into your browser:\r\n\r\n{{{ url("/mship/security/forgotten-link", ["code" => $data->token->code]) }}}\r\n\r\nIf you have any problems with this reset, please respond to this email with full details of the error you''ve experienced.', 50, 0, 'web-support@vatsim-uk.co.uk', 1, '2015-01-04 01:58:17', '2015-01-04 01:58:17', NULL),
(3, 'MSHIP', 'SECURITY', 'RESET', 'SSO Security Password Reset', 'Dear {{{ $recipient->name }}},\r\n\r\nYou, or somebody posing as you, requested a new secondary password. Details are as follows:\r\n\r\nIP: {{{ $data->ip }}}\r\nPassword: {{{ $data->password }}}\r\n\r\nWhen you next login to the VATSIM UK system, you will be required to change this password.\r\n', 50, 1, 'web-support@vatsim-uk.co.uk', 1, '2015-01-04 02:31:43', '2015-01-04 02:31:43', NULL);