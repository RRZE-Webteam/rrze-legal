<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

$data = [
    'version' => 8,
    'items' => [
        'default' => [
            'id' => 'default',
            'cookie_name' => 'rrze-legal-consent',
            'category' => 'essential',
            'name' => __('Default Cookie', 'rrze-legal'),
            'provider' => __('Owner of this website', 'rrze-legal'),
            'purpose' => __('Saves the visitors preferences selected in the Consent Banner.', 'rrze-legal'),
            'privacy_policy_url' => tos()->endpointUrl('privacy'),
            'hosts' => tos()->getSiteUrlHost(),
            'cookie_expiry' => __('1 Year', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 1,
            'status' => true,
            'static' => true,
        ],
        'wordpress' => [
            'id' => 'wordpress',
            'cookie_name' => 'wordpress_[*]',
            'category' => 'essential',
            'name' => __('WordPress', 'rrze-legal'),
            'provider' => __('No transmission to third parties', 'rrze-legal'),
            'purpose' => __('Test if cookie can be set. Remember User session.', 'rrze-legal'),
            'privacy_policy_url' => tos()->endpointUrl('privacy'),
            'hosts' => '.' . tos()->getSiteUrlHost(),
            'cookie_expiry' => __('Session', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 2,
            'status' => true,
            'static' => true,
        ],
        'simplesamlsessionid' => [
            'id' => 'simplesamlsessionid',
            'cookie_name' => 'SimpleSAMLSessionID,SimpleSAMLAuthToken',
            'category' => 'essential',
            'name' => __('SimpleSAML', 'rrze-legal'),
            'provider' => __('No transmission to third parties', 'rrze-legal'),
            'purpose' => __('Used to manage WebSSO session state.', 'rrze-legal'),
            'privacy_policy_url' => tos()->endpointUrl('privacy'),
            'hosts' => tos()->getSiteUrlHost(),
            'cookie_expiry' => __('Session', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 3,
            'plugin_slug' => 'rrze-sso/rrze-sso.php',
            'status' => consent()->isServiceProviderActive('simplesamlsessionid'),
            'static' => true,
        ],
        'phpsessid' => [
            'id' => 'phpsessid',
            'cookie_name' => 'PHPSESSID',
            'category' => 'essential',
            'name' => __('PHPSESSID', 'rrze-legal'),
            'provider' => __('No transmission to third parties', 'rrze-legal'),
            'purpose' => __('Preserves user session state across page requests.', 'rrze-legal'),
            'privacy_policy_url' => tos()->endpointUrl('privacy'),
            'hosts' => tos()->getSiteUrlHost(),
            'cookie_expiry' => __('Session', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 4,
            'status' => true,
            'static' => true,
        ],
        'rrze_rsvp' => [
            'id' => 'rrze_rsvp',
            'cookie_name' => 'rrze_rsvp',
            'category' => 'essential',
            'name' => __('RSVP', 'rrze-legal'),
            'provider' => __('No transmission to third parties', 'rrze-legal'),
            'purpose' => __('Used to manage RSVP session state.', 'rrze-legal'),
            'privacy_policy_url' => tos()->endpointUrl('privacy'),
            'hosts' => tos()->getSiteUrlHost(),
            'cookie_expiry' => __('Session', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 5,
            'plugin_slug' => 'rrze-rsvp/rrze-rsvp.php',
            'status' => consent()->isServiceProviderActive('rrze_rsvp'),
            'static' => true,
        ],
        'rrze_ratebutton' => [
            'id' => 'rrze_ratebutton',
            'cookie_name' => 'rrze_rated_[*]',
            'category' => 'essential',
            'name' => __('RRZE Rate Button', 'rrze-legal'),
            'provider' => __('No transmission to third parties', 'rrze-legal'),
            'purpose' => __('Used to manage the rating counter.', 'rrze-legal'),
            'privacy_policy_url' => tos()->endpointUrl('privacy'),
            'hosts' => '.' . tos()->getSiteUrlHost(),
            'cookie_expiry' => __('1 Year', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 6,
            'plugin_slug' => 'rrze_ratebutton/rrze_ratebutton.php',
            'status' => consent()->isServiceProviderActive('rrze_ratebutton'),
            'static' => true,
        ],
        'rrze_appointment' => [
            'id' => 'rrze_appointment',
            'cookie_name' => '',
            'category' => 'essential',
            'name' => __('RRZE Appointment', 'rrze-legal'),
            'provider' => __('No transmission to third parties', 'rrze-legal'),
            'purpose' => __('Used to manage digital appointment and office-hour booking.', 'rrze-legal'),
            'privacy_text_de' => <<<'TEXT'
Auf unserer Internetseite kann das Plugin RRZE Appointment für die digitale Termin- und Sprechstundenvergabe genutzt werden. Nimmt ein Nutzer diese Möglichkeit wahr, so werden die in der Eingabemaske eingegebenen Daten an uns übermittelt und gespeichert. Es erfolgt keine Datenübertragung an Dritte.

Rechtsgrundlage für die Verarbeitung der Daten ist Art. 6 Abs. 1 lit. e DSGVO i.V.m. Art. 4 und 5 BayDSG zur Erfüllung unserer Aufgaben. Zielt die Terminvergabe auf den Abschluss oder die Durchführung eines Vertrages ab, so ist zusätzliche Rechtsgrundlage für die Verarbeitung Art. 6 Abs. 1 lit. b DSGVO.

Die Verarbeitung der personenbezogenen Daten aus der Eingabemaske dient uns allein zur Bearbeitung der Terminvergabe.

Die Daten werden gelöscht, sobald sie für die Erreichung des Zweckes ihrer Erhebung nicht mehr erforderlich sind. Dies ist dann der Fall, wenn der jeweilige Termin oder die jeweilige Anfrage abschließend bearbeitet wurde und keine gesetzlichen Aufbewahrungspflichten entgegenstehen.

Aus Gründen, die sich aus Ihrer besonderen Situation ergeben, können Sie der Verarbeitung Sie betreffender personenbezogener Daten durch uns jederzeit widersprechen (Art. 21 DSGVO). Sofern die gesetzlichen Voraussetzungen vorliegen, verarbeiten wir Ihre personenbezogenen Daten nicht mehr.

Soweit die für die Terminvergabe erforderlichen personenbezogenen Daten nicht angegeben werden, ist eine Terminvergabe nicht möglich.

Durch RRZE Appointment werden keine Cookies gesetzt.
TEXT,
            'privacy_text_en' => <<<'TEXT'
The RRZE Appointment plugin may be used on our website for digital appointment and office-hour booking. If a user makes use of this possibility, the data they enter in the input form are transmitted to us and stored. No data are transmitted to third parties.

The legal basis for processing the data is Article 6 (1) lit. e GDPR in conjunction with Art. 4 and 5 BayDSG for the fulfilment of our tasks. If the appointment booking aims to conclude or perform a contract, the additional legal basis for processing is Art. 6 (1) lit. b GDPR.

The personal data from the input form are processed solely for the purpose of handling the appointment booking.

Data are deleted as soon as they are no longer necessary for fulfilling the purpose for which they were collected. This is the case when the respective appointment or request has been conclusively processed and no statutory retention obligations apply.

For reasons that arise from your particular situation, you may object to the processing of personal data relating to you by us at any time (Art. 21 GDPR). If the legal requirements are met, we will no longer process your personal data.

If the personal data required for appointment booking are not provided, appointment booking is not possible.

RRZE Appointment does not set cookies.
TEXT,
            'privacy_policy_url' => tos()->endpointUrl('privacy'),
            'hosts' => tos()->getSiteUrlHost(),
            'cookie_expiry' => '',
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 7,
            'plugin_slug' => 'rrze-appointment/rrze-appointment.php',
            'status' => consent()->isServiceProviderActive('rrze_appointment'),
            'static' => true,
        ],
        'siteimprove_analytics' => [
            'id' => 'siteimprove_analytics',
            'cookie_name' => 'nmstat',
            'category' => 'statistics',
            'name' => __('Siteimprove Analytics', 'rrze-legal'),
            'provider' => __('Rosenheimer Str. 143 C, 81671 Munich, Germany', 'rrze-legal'),
            'purpose' => __('Used to help record the visitor’s use of the website.', 'rrze-legal'),
            'privacy_text_de' => <<<'TEXT'
Diese Website benutzt Siteimprove Analytics, einen Webanalysedienst, der von Siteimprove zur Verfügung gestellt wird. Siteimprove Analytics nutzt Cookies, um die Nutzung der Website zu analysieren und die Qualität des Angebots zu verbessern. Die durch die Cookies erzeugten Informationen zur Website-Nutzung werden von Siteimprove auf Servern in Dänemark gespeichert und verarbeitet.

IP-Adressen werden vollständig anonymisiert, bevor erhobene Daten über die Siteimprove Suite einsehbar sind. Eine Umkehrung der Anonymisierung und eine Zuordnung der IP-Adressen zu erhobenen Daten ist nicht möglich.

Die erhobenen Informationen werden genutzt, um das Benutzerverhalten auszuwerten, Berichte über die Website-Nutzung zu erstellen und das Websiteangebot zu verbessern. Siteimprove gibt diese Informationen nicht an Dritte weiter und nutzt sie nicht für Marketing- oder Werbezwecke.

Rechtsgrundlage für die Verarbeitung personenbezogener Daten unter Verwendung von Cookies ist Art. 6 Abs. 1 lit. a DSGVO, sofern eine Einwilligung eingeholt wurde. Die Einwilligung ist jederzeit widerrufbar.
TEXT,
            'privacy_text_en' => <<<'TEXT'
This website uses Siteimprove Analytics, a web analytics service provided by Siteimprove. Siteimprove Analytics uses cookies to analyze website usage and improve the quality of the service. The information generated by the cookies about website usage is stored and processed by Siteimprove on servers in Denmark.

IP addresses are fully anonymized before collected data is made available in the Siteimprove Suite. Reversing the anonymization and assigning IP addresses to collected data is not possible.

The collected information is used to evaluate visitor behavior, create reports about website usage and improve the website. Siteimprove does not disclose this information to third parties and does not use it for marketing or advertising purposes.

The legal basis for processing personal data using cookies is Art. 6 (1) (a) GDPR, provided that consent has been obtained. Consent can be withdrawn at any time.
TEXT,
            'privacy_policy_url' => 'https://www.siteimprove.com/privacy/privacy-policy/',
            'hosts' => 'siteimprove.com',
            'cookie_expiry' => __('1000 Days', 'rrze-legal'),
            'enqueued_script_handles' => 'rrze-siteimprove-analytics',
            'block_enqueued_script' => true,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '<script>window.RRZELegal.unblockScriptBlockerId("siteimprove_analytics");</script>',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 1,
            'plugin_slug' => 'rrze-siteimprove/rrze-siteimprove.php',
            'status' => consent()->isServiceProviderActive('siteimprove_analytics'),
            'static' => true,
        ],
        'twitter' => [
            'id' => 'twitter',
            'cookie_name' => '__widgetsettings, local_storage_support_test',
            'category' => 'external_media',
            'name' => __('Twitter', 'rrze-legal'),
            'provider' => __('Twitter International Company, One Cumberland Place, Fenian Street, Dublin 2, D02 AX07, Ireland', 'rrze-legal'),
            'purpose' => __('Used to unblock Twitter content.', 'rrze-legal'),
            'privacy_text_de' => <<<'TEXT'
Auf dieser Website können Inhalte von Twitter eingebunden sein. Wenn solche Inhalte aktiviert werden, kann eine Verbindung zu Servern von Twitter hergestellt werden. Dabei können personenbezogene Daten wie IP-Adresse, Browserinformationen und Informationen zur aufgerufenen Seite an Twitter übermittelt werden.

Wenn Sie bei Twitter angemeldet sind, kann Twitter den Aufruf Ihrem Benutzerkonto zuordnen. Weitere Informationen finden Sie in der Datenschutzerklärung des Anbieters.
TEXT,
            'privacy_text_en' => <<<'TEXT'
This website may include content from Twitter. If such content is activated, a connection to Twitter servers may be established. Personal data such as IP address, browser information and information about the page accessed may be transmitted to Twitter.

If you are logged in to Twitter, Twitter may associate the visit with your user account. Further information can be found in the provider's privacy policy.
TEXT,
            'privacy_policy_url' => 'https://twitter.com/privacy',
            'hosts' => implode(PHP_EOL, [
                'twimg.com',
                'twitter.com',
            ]),
            'cookie_expiry' => __('Unlimited', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '<script>if(typeof window.RRZELegal === "object") { window.RRZELegal.unblockContentId("twitter"); }</script>',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 1,
            'status' => true,
            'static' => true,
        ],
        'youtube' => [
            'id' => 'youtube',
            'cookie_name' => 'NID',
            'category' => 'external_media',
            'name' => __('YouTube', 'rrze-legal'),
            'provider' => __('Google Ireland Limited, Gordon House, Barrow Street, Dublin 4, Ireland', 'rrze-legal'),
            'purpose' => __('Used to unblock YouTube content.', 'rrze-legal'),
            'privacy_text_de' => <<<'TEXT'
Diese Website bindet Videos des Videoportals YouTube ein. Anbieter ist Google Ireland Limited. Wenn Sie eine Seite aufrufen, auf der YouTube eingebunden ist, kann eine Verbindung zu Servern von YouTube hergestellt werden. Dabei kann YouTube Informationen darüber erhalten, welche Seite Sie besucht haben.

Die Einbindung von YouTube-Videos kann über ein Plugin erfolgen, das eine Datenübertragung erst nach dem Start des Videos auslöst, über den Dienst youtube-nocookie.com oder über den originalen Einbettungscode von YouTube. Beim Abruf des Videos kann YouTube Cookies auf Ihrem Endgerät speichern.

Wenn Sie in Ihrem YouTube-Konto angemeldet sind, kann YouTube Ihr Surfverhalten direkt Ihrem persönlichen Profil zuordnen. Dies können Sie verhindern, indem Sie sich aus Ihrem YouTube-Konto ausloggen.

Die Nutzung erfolgt auf Grundlage Ihrer Einwilligung nach Art. 6 Abs. 1 lit. a DSGVO. Die Einwilligung ist jederzeit widerrufbar.
TEXT,
            'privacy_text_en' => <<<'TEXT'
This website embeds videos from the YouTube video portal. The provider is Google Ireland Limited. When you visit a page on which YouTube is embedded, a connection to YouTube servers may be established. YouTube may receive information about which page you have visited.

YouTube videos may be embedded using a plugin that only starts data transmission after the video is played, via the youtube-nocookie.com service, or using YouTube's original embed code. When the video is accessed, YouTube may store cookies on your device.

If you are logged in to your YouTube account, YouTube may associate your browsing behavior directly with your personal profile. You can prevent this by logging out of your YouTube account.

The use is based on your consent pursuant to Art. 6 (1) (a) GDPR. Consent can be withdrawn at any time.
TEXT,
            'privacy_policy_url' => 'https://policies.google.com/privacy?hl=en&gl=en',
            'hosts' => implode(PHP_EOL, [
                'google.com',
                'youtube.com',
                'youtube-nocookie.com',
            ]),
            'cookie_expiry' => __('6 Months', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '<script>if(typeof window.RRZELegal === "object") { window.RRZELegal.unblockContentId("youtube"); }</script>',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 2,
            'status' => true,
            'static' => true,
        ],
        'vimeo' => [
            'id' => 'vimeo',
            'cookie_name' => 'vuid',
            'category' => 'external_media',
            'name' => __('Vimeo', 'rrze-legal'),
            'provider' => __('Vimeo Inc., 555 West 18th Street, New York, New York 10011, USA', 'rrze-legal'),
            'purpose' => __('Used to unblock Vimeo content.', 'rrze-legal'),
            'privacy_text_de' => <<<'TEXT'
Diese Website bindet Videos des Videoportals Vimeo ein. Anbieter ist Vimeo Inc. Wenn Sie eine Seite aufrufen, auf der Vimeo eingebunden ist, kann eine Verbindung zu Servern von Vimeo hergestellt werden. Dabei kann Vimeo Informationen darüber erhalten, welche Seite Sie besucht haben, und Ihre IP-Adresse verarbeiten.

Wenn Sie in Ihrem Vimeo-Konto angemeldet sind, kann Vimeo Ihr Surfverhalten direkt Ihrem persönlichen Profil zuordnen. Dies können Sie verhindern, indem Sie sich aus Ihrem Vimeo-Konto ausloggen.

Die Nutzung erfolgt auf Grundlage Ihrer Einwilligung nach Art. 6 Abs. 1 lit. a DSGVO. Die Einwilligung ist jederzeit widerrufbar.
TEXT,
            'privacy_text_en' => <<<'TEXT'
This website embeds videos from the Vimeo video portal. The provider is Vimeo Inc. When you visit a page on which Vimeo is embedded, a connection to Vimeo servers may be established. Vimeo may receive information about which page you have visited and may process your IP address.

If you are logged in to your Vimeo account, Vimeo may associate your browsing behavior directly with your personal profile. You can prevent this by logging out of your Vimeo account.

The use is based on your consent pursuant to Art. 6 (1) (a) GDPR. Consent can be withdrawn at any time.
TEXT,
            'privacy_policy_url' => 'https://vimeo.com/privacy',
            'hosts' => 'player.vimeo.com',
            'cookie_expiry' => __('2 Years', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '<script>if(typeof window.RRZELegal === "object") { window.RRZELegal.unblockContentId("vimeo"); }</script>',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 3,
            'status' => true,
            'static' => true,
        ],
        'slideshare' => [
            'id' => 'slideshare',
            'cookie_name' => '__utma',
            'category' => 'external_media',
            'name' => __('Slideshare', 'rrze-legal'),
            'provider' => __('Scribd, Inc., 460 Bryant St, 100, San Francisco, CA 94107-2594 USA', 'rrze-legal'),
            'purpose' => __('Used to unblock Slideshare content.', 'rrze-legal'),
            'privacy_text_de' => <<<'TEXT'
Diese Website kann Präsentationen über das Online-Portal Slideshare einbinden. Betreiber ist Scribd, Inc. Wenn eine Seite mit eingebundenen Slideshare-Inhalten aufgerufen und der Inhalt aktiviert wird, kann eine direkte Verbindung zu Servern des Anbieters hergestellt werden.

Dabei können Nutzungsdaten, Geräteinformationen, IP-Adressen und Cookie-Informationen verarbeitet werden. Zweck und Umfang der Datenverarbeitung sowie Ihre Rechte und Einstellungsmöglichkeiten ergeben sich aus der Datenschutzerklärung des Anbieters.

Die Nutzung erfolgt auf Grundlage Ihrer Einwilligung nach Art. 6 Abs. 1 lit. a DSGVO. Die Einwilligung ist jederzeit widerrufbar.
TEXT,
            'privacy_text_en' => <<<'TEXT'
This website may embed presentations via the Slideshare online portal. The provider is Scribd, Inc. If a page with embedded Slideshare content is accessed and the content is activated, a direct connection to the provider's servers may be established.

Usage data, device information, IP addresses and cookie information may be processed. The purpose and scope of data processing as well as your rights and settings options are described in the provider's privacy policy.

The use is based on your consent pursuant to Art. 6 (1) (a) GDPR. Consent can be withdrawn at any time.
TEXT,
            'privacy_policy_url' => 'https://www.slideshare.net/privacy',
            'hosts' => 'www.slideshare.net',
            'cookie_expiry' => __('2 Years', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '<script>if(typeof window.RRZELegal === "object") { window.RRZELegal.unblockContentId("slideshare"); }</script>',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 4,
            'status' => true,
            'static' => true,
        ],
        'brmediathek' => [
            'id' => 'brmediathek',
            'cookie_name' => 'atid',
            'category' => 'external_media',
            'name' => __('BR Mediathek', 'rrze-legal'),
            'provider' => __('Bayerischer Rundfunk, Rundfunkplatz 1, 80335 Munich, Germany', 'rrze-legal'),
            'purpose' => __('Used to unblock BR content.', 'rrze-legal'),
            'privacy_text_de' => <<<'TEXT'
Diese Website kann Inhalte der BR Mediathek einbinden. Wenn solche Inhalte aktiviert werden, kann eine Verbindung zu Servern des Bayerischen Rundfunks hergestellt werden. Dabei können personenbezogene Daten wie IP-Adresse, Browserinformationen und Informationen zur aufgerufenen Seite verarbeitet werden.

Die Nutzung erfolgt auf Grundlage Ihrer Einwilligung nach Art. 6 Abs. 1 lit. a DSGVO. Die Einwilligung ist jederzeit widerrufbar.
TEXT,
            'privacy_text_en' => <<<'TEXT'
This website may embed content from BR Mediathek. If such content is activated, a connection to servers of Bayerischer Rundfunk may be established. Personal data such as IP address, browser information and information about the page accessed may be processed.

The use is based on your consent pursuant to Art. 6 (1) (a) GDPR. Consent can be withdrawn at any time.
TEXT,
            'privacy_policy_url' => 'https://www.br.de/unternehmen/service/impressum/impressum-datenschutzerklaerung-unternehmen-v2-100.html',
            'hosts' => implode(PHP_EOL, [
                'www.br.de',
            ]),
            'cookie_expiry' => __('1 Year', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '<script>if(typeof window.RRZELegal === "object") { window.RRZELegal.unblockContentId("brmediathek"); }</script>',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 5,
            'plugin_slug' => 'rrze-video/rrze-video.php',
            'status' => consent()->isServiceProviderActive('brmediathek'),
            'static' => true,
        ],
        'ardmediathek' => [
            'id' => 'ardmediathek',
            'cookie_name' => 'atidvisitor',
            'category' => 'external_media',
            'name' => __('ARD Mediathek', 'rrze-legal'),
            'provider' => __('Bayerischer Rundfunk, Rundfunkplatz 1, 80335 Munich, Germany', 'rrze-legal'),
            'purpose' => __('Used to unblock ARD content.', 'rrze-legal'),
            'privacy_text_de' => <<<'TEXT'
Diese Website kann Inhalte der ARD Mediathek einbinden. Wenn solche Inhalte aktiviert werden, kann eine Verbindung zu Servern des Anbieters hergestellt werden. Dabei können personenbezogene Daten wie IP-Adresse, Browserinformationen und Informationen zur aufgerufenen Seite verarbeitet werden.

Die Nutzung erfolgt auf Grundlage Ihrer Einwilligung nach Art. 6 Abs. 1 lit. a DSGVO. Die Einwilligung ist jederzeit widerrufbar.
TEXT,
            'privacy_text_en' => <<<'TEXT'
This website may embed content from ARD Mediathek. If such content is activated, a connection to the provider's servers may be established. Personal data such as IP address, browser information and information about the page accessed may be processed.

The use is based on your consent pursuant to Art. 6 (1) (a) GDPR. Consent can be withdrawn at any time.
TEXT,
            'privacy_policy_url' => 'https://www.ardmediathek.de/datenschutz',
            'hosts' => implode(PHP_EOL, [
                'www.ardmediathek.de',
            ]),
            'cookie_expiry' => __('1 Year', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '<script>if(typeof window.RRZELegal === "object") { window.RRZELegal.unblockContentId("ardmediathek"); }</script>',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 6,
            'plugin_slug' => 'rrze-video/rrze-video.php',
            'status' => consent()->isServiceProviderActive('ardmediathek'),
            'static' => true,
        ],
    ],
];
