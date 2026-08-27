<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

$data = [
    'version' => 13,
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
            'cookie_expiry' => __('6 Months', 'rrze-legal'),
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
            'category' => 'functional',
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
            'category' => 'functional',
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
            'category' => 'functional',
            'name' => __('RRZE Appointment', 'rrze-legal'),
            'provider' => __('No transmission to third parties', 'rrze-legal'),
            'purpose' => __('Used to manage digital appointment and office-hour booking.', 'rrze-legal'),
            'privacy_text_de' => 'Auf unserer Internetseite kann das Plugin RRZE Appointment für die digitale Termin- und Sprechstundenvergabe genutzt werden. Nimmt ein Nutzer diese Möglichkeit wahr, so werden die in der Eingabemaske eingegebenen Daten an uns übermittelt und gespeichert. Es erfolgt keine Datenübertragung an Dritte.

Rechtsgrundlage für die Verarbeitung der Daten ist Art. 6 Abs. 1 lit. e DSGVO i.V.m. Art. 4 und 5 BayDSG zur Erfüllung unserer Aufgaben. Zielt die Terminvergabe auf den Abschluss oder die Durchführung eines Vertrages ab, so ist zusätzliche Rechtsgrundlage für die Verarbeitung Art. 6 Abs. 1 lit. b DSGVO.

Die Verarbeitung der personenbezogenen Daten aus der Eingabemaske dient uns allein zur Bearbeitung der Terminvergabe.

Die Daten werden gelöscht, sobald sie für die Erreichung des Zweckes ihrer Erhebung nicht mehr erforderlich sind. Dies ist dann der Fall, wenn der jeweilige Termin oder die jeweilige Anfrage abschließend bearbeitet wurde und keine gesetzlichen Aufbewahrungspflichten entgegenstehen.

Aus Gründen, die sich aus Ihrer besonderen Situation ergeben, können Sie der Verarbeitung Sie betreffender personenbezogener Daten durch uns jederzeit widersprechen (Art. 21 DSGVO). Sofern die gesetzlichen Voraussetzungen vorliegen, verarbeiten wir Ihre personenbezogenen Daten nicht mehr.

Soweit die für die Terminvergabe erforderlichen personenbezogenen Daten nicht angegeben werden, ist eine Terminvergabe nicht möglich.

Durch RRZE Appointment werden keine Cookies gesetzt.',
            'privacy_text_en' => 'The RRZE Appointment plugin may be used on our website for digital appointment and office-hour booking. If a user makes use of this possibility, the data they enter in the input form are transmitted to us and stored. No data are transmitted to third parties.

The legal basis for processing the data is Article 6 (1) lit. e GDPR in conjunction with Art. 4 and 5 BayDSG for the fulfilment of our tasks. If the appointment booking aims to conclude or perform a contract, the additional legal basis for processing is Art. 6 (1) lit. b GDPR.

The personal data from the input form are processed solely for the purpose of handling the appointment booking.

Data are deleted as soon as they are no longer necessary for fulfilling the purpose for which they were collected. This is the case when the respective appointment or request has been conclusively processed and no statutory retention obligations apply.

For reasons that arise from your particular situation, you may object to the processing of personal data relating to you by us at any time (Art. 21 GDPR). If the legal requirements are met, we will no longer process your personal data.

If the personal data required for appointment booking are not provided, appointment booking is not possible.

RRZE Appointment does not set cookies.',
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
            'privacy_text_de' => 'Diese Website benutzt Siteimprove Analytics, einen Webanalysedienst, der von Siteimprove zur Verfügung gestellt wird. Siteimprove Analytics nutzt Cookies, um die Nutzung der Website zu analysieren und die Qualität des Angebots zu verbessern. Die durch die Cookies erzeugten Informationen zur Website-Nutzung werden von Siteimprove auf Servern in Dänemark gespeichert und verarbeitet.

IP-Adressen werden vollständig anonymisiert, bevor erhobene Daten über die Siteimprove Suite einsehbar sind. Eine Umkehrung der Anonymisierung und eine Zuordnung der IP-Adressen zu erhobenen Daten ist nicht möglich.

Die erhobenen Informationen werden genutzt, um das Benutzerverhalten auszuwerten, Berichte über die Website-Nutzung zu erstellen und das Websiteangebot zu verbessern. Siteimprove gibt diese Informationen nicht an Dritte weiter und nutzt sie nicht für Marketing- oder Werbezwecke.

Rechtsgrundlage für die Verarbeitung personenbezogener Daten unter Verwendung von Cookies ist Art. 6 Abs. 1 lit. a DSGVO, sofern eine Einwilligung eingeholt wurde. Die Einwilligung ist jederzeit widerrufbar.',
            'privacy_text_en' => 'This website uses Siteimprove Analytics, a web analytics service provided by Siteimprove. Siteimprove Analytics uses cookies to analyze website usage and improve the quality of the service. The information generated by the cookies about website usage is stored and processed by Siteimprove on servers in Denmark.

IP addresses are fully anonymized before collected data is made available in the Siteimprove Suite. Reversing the anonymization and assigning IP addresses to collected data is not possible.

The collected information is used to evaluate visitor behavior, create reports about website usage and improve the website. Siteimprove does not disclose this information to third parties and does not use it for marketing or advertising purposes.

The legal basis for processing personal data using cookies is Art. 6 (1) (a) GDPR, provided that consent has been obtained. Consent can be withdrawn at any time.',
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
        'jobspreader_analytics' => [
            'id' => 'jobspreader_analytics',
            'cookie_name' => 'js_clid, js_startqueue, js_endqueue, js_end',
            'category' => 'statistics',
            'name' => __('Jobspreader Analytics', 'rrze-legal'),
            'provider' => __('Wollmilchsau GmbH, Koppel 97, 20099 Hamburg, Germany', 'rrze-legal'),
            'purpose' => __('Used to analyse the performance of job advertisements and application processes.', 'rrze-legal'),
            'privacy_text_de' => 'Diese Website kann Jobspreader Analytics einsetzen, einen Analysedienst der Wollmilchsau GmbH. Jobspreader Analytics dient der Auswertung und Optimierung von Stellenanzeigen und Bewerbungsprozessen.

Wenn Sie über eine von Jobspreader verbreitete Stellenanzeige auf die Karriereseite gelangen und eingewilligt haben, verarbeitet Jobspreader Analytics eine zufällig erzeugte Click-ID, die aufgerufene Job-URL sowie Zeitstempel. Zudem kann erfasst werden, ob ein Bewerbungsprozess gestartet oder abgeschlossen wurde. Die Daten werden verwendet, um die Leistung von Jobkampagnen und die Nutzerfreundlichkeit des Bewerbungsprozesses zu bewerten und zu verbessern.

Jobspreader Analytics setzt die Cookies js_clid, js_startqueue, js_endqueue und js_end. Das Cookie js_clid wird 30 Tage gespeichert. Die übrigen Cookies werden nur vorübergehend für die jeweiligen Ereignisse verwendet und anschließend gelöscht.

Die Nutzung erfolgt nur auf Grundlage Ihrer Einwilligung nach Art. 6 Abs. 1 lit. a DSGVO und § 25 Abs. 1 TDDDG. Die Einwilligung ist jederzeit widerrufbar.',
            'privacy_text_en' => 'This website may use Jobspreader Analytics, an analytics service provided by Wollmilchsau GmbH. Jobspreader Analytics is used to analyse and optimise job advertisements and application processes.

If you reach the careers website through a job advertisement distributed by Jobspreader and have given your consent, Jobspreader Analytics processes a randomly generated click ID, the job URL accessed and timestamps. It may also record whether an application process was started or completed. The data are used to evaluate and improve the performance of job campaigns and the usability of the application process.

Jobspreader Analytics sets the cookies js_clid, js_startqueue, js_endqueue and js_end. The js_clid cookie is stored for 30 days. The remaining cookies are used temporarily for the respective events and are deleted afterwards.

The service is used only on the basis of your consent pursuant to Art. 6 (1) (a) GDPR and Section 25 (1) TDDDG. Consent can be withdrawn at any time.',
            'privacy_policy_url' => 'https://wollmilchsau.de/datenschutz/',
            'hosts' => 'jobspreader.com',
            'cookie_expiry' => __('30 Days (js_clid); otherwise Session', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 2,
            'plugin_slug' => 'jobspreader-analytics/jobspreader-analytics.php',
            'status' => consent()->isServiceProviderActive('jobspreader_analytics'),
            'static' => true,
        ],
        'twitter' => [
            'id' => 'twitter',
            'cookie_name' => 'guest_id, personalization_id, app_shell_referrer, _sl, __utma, _ga_',
            'category' => 'external_media',
            'name' => __('X (formerly Twitter)', 'rrze-legal'),
            'provider' => __('X Internet Unlimited Company, One Cumberland Place, Fenian Street, Dublin 2, D02 AX07, Ireland', 'rrze-legal'),
            'purpose' => __('Used to unblock X content.', 'rrze-legal'),
            'privacy_text_de' => 'Auf dieser Website können Inhalte von X (vormals Twitter) eingebunden sein. Wenn solche Inhalte aktiviert werden, kann eine Verbindung zu Servern von X hergestellt werden. Dabei können personenbezogene Daten wie IP-Adresse, Browserinformationen und Informationen zur aufgerufenen Seite an X übermittelt werden.

Wenn Sie bei X angemeldet sind, kann X den Aufruf Ihrem Benutzerkonto zuordnen. Weitere Informationen finden Sie in der Datenschutzerklärung des Anbieters.',
            'privacy_text_en' => 'This website may include content from X (formerly Twitter). If such content is activated, a connection to X servers may be established. Personal data such as IP address, browser information and information about the page accessed may be transmitted to X.

If you are logged in to X, X may associate the visit with your user account. Further information can be found in the provider\'s privacy policy.',
            'privacy_policy_url' => 'https://x.com/en/privacy',
            'hosts' => implode(PHP_EOL, [
                'x.com',
                'twimg.com',
                'twitter.com',
                't.co',
                'platform.twitter.com',
                'syndication.twitter.com',
            ]),
            'cookie_expiry' => __('Up to 13 months', 'rrze-legal'),
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
            'cookie_name' => 'NID, __Secure-ENID, __Secure-YNID, __Secure-YENID, PREF, pm_sess, SOCS',
            'category' => 'external_media',
            'name' => __('YouTube', 'rrze-legal'),
            'provider' => __('Google Ireland Limited, Gordon House, Barrow Street, Dublin 4, Ireland', 'rrze-legal'),
            'purpose' => __('Used to unblock YouTube content.', 'rrze-legal'),
            'privacy_text_de' => 'Diese Website bindet Videos des Videoportals YouTube ein. Anbieter ist Google Ireland Limited. Wenn Sie eine Seite aufrufen, auf der YouTube eingebunden ist, kann eine Verbindung zu Servern von YouTube hergestellt werden. Dabei kann YouTube Informationen darüber erhalten, welche Seite Sie besucht haben.

Die Einbindung von YouTube-Videos kann über ein Plugin erfolgen, das eine Datenübertragung erst nach dem Start des Videos auslöst, über den Dienst youtube-nocookie.com oder über den originalen Einbettungscode von YouTube. Beim Abruf des Videos kann YouTube Cookies auf Ihrem Endgerät speichern.

Wenn Sie in Ihrem YouTube-Konto angemeldet sind, kann YouTube Ihr Surfverhalten direkt Ihrem persönlichen Profil zuordnen. Dies können Sie verhindern, indem Sie sich aus Ihrem YouTube-Konto ausloggen.

Die Nutzung erfolgt auf Grundlage Ihrer Einwilligung nach Art. 6 Abs. 1 lit. a DSGVO. Die Einwilligung ist jederzeit widerrufbar.',
            'privacy_text_en' => 'This website embeds videos from the YouTube video portal. The provider is Google Ireland Limited. When you visit a page on which YouTube is embedded, a connection to YouTube servers may be established. YouTube may receive information about which page you have visited.

YouTube videos may be embedded using a plugin that only starts data transmission after the video is played, via the youtube-nocookie.com service, or using YouTube\'s original embed code. When the video is accessed, YouTube may store cookies on your device.

If you are logged in to your YouTube account, YouTube may associate your browsing behavior directly with your personal profile. You can prevent this by logging out of your YouTube account.

The use is based on your consent pursuant to Art. 6 (1) (a) GDPR. Consent can be withdrawn at any time.',
            'privacy_policy_url' => 'https://policies.google.com/privacy?hl=en&gl=en',
            'hosts' => implode(PHP_EOL, [
                'google.com',
                'googlevideo.com',
                'gstatic.com',
                'youtube.com',
                'youtube-nocookie.com',
                'youtu.be',
                'ytimg.com',
            ]),
            'cookie_expiry' => __('Up to 13 months', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '<script>if(typeof window.RRZELegal === "object") { window.RRZELegal.unblockContentId("youtube"); }</script>',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 2,
            'plugin_slug' => 'rrze-video/rrze-video.php',
            'status' => consent()->isServiceProviderActive('youtube'),
            'static' => true,
        ],
        'spotify' => [
            'id' => 'spotify',
            'cookie_name' => 'sp_t, sp_landing',
            'category' => 'external_media',
            'name' => __('Spotify', 'rrze-legal'),
            'provider' => __('Spotify AB, Regeringsgatan 19, 111 53 Stockholm, Sweden', 'rrze-legal'),
            'purpose' => __('Used to unblock Spotify audio content.', 'rrze-legal'),
            'privacy_text_de' => 'Unsere Website kann Inhalte der Audio-Streaming-Plattform Spotify einbinden. Anbieter ist Spotify AB, Regeringsgatan 19, 111 53 Stockholm, Schweden.

Spotify-Inhalte können als eingebetteter iFrame auf unserer Website angezeigt werden, zum Beispiel einzelne Audiodateien, Alben, Playlisten oder Podcasts. Wenn Sie eine Seite besuchen, auf der ein Spotify-Inhalt eingebettet ist und Sie diesen Inhalt aktivieren, wird eine Verbindung zu Servern von Spotify hergestellt und der Inhalt innerhalb unserer Website dargestellt.

Dabei kann Spotify Informationen darüber erhalten, welche Seite Sie besucht haben. Gegebenenfalls wird auch Ihre IP-Adresse an Spotify übertragen. Wenn Sie eingebettete Audiodateien, Alben, Playlisten oder Podcasts abspielen, kann auch diese Information an Spotify weitergegeben werden. Sofern Sie dabei in Ihrem Spotify-Konto angemeldet sind, kann Spotify diese Daten Ihrem Benutzerkonto zuordnen. Sie können dies verhindern, indem Sie sich aus Ihrem Spotify-Konto ausloggen.

Die Nutzung erfolgt auf Grundlage Ihrer Einwilligung nach Art. 6 Abs. 1 lit. a DSGVO. Die Einwilligung ist jederzeit widerrufbar.',
            'privacy_text_en' => 'Our website may embed content from the audio streaming platform Spotify. The provider is Spotify AB, Regeringsgatan 19, 111 53 Stockholm, Sweden.

Spotify content may be displayed on our website as an embedded iframe, for example individual audio files, albums, playlists or podcasts. If you visit a page on which Spotify content is embedded and activate this content, a connection to Spotify servers is established and the content is displayed within our website.

Spotify may receive information about which page you have visited. Your IP address may also be transmitted to Spotify. If you play embedded audio files, albums, playlists or podcasts, this information may also be transmitted to Spotify. If you are logged in to your Spotify account, Spotify may associate this data with your user account. You can prevent this by logging out of your Spotify account.

The use is based on your consent pursuant to Art. 6 (1) (a) GDPR. Consent can be withdrawn at any time.',
            'privacy_policy_url' => 'https://www.spotify.com/legal/privacy-policy/',
            'hosts' => implode(PHP_EOL, [
                'open.spotify.com',
                'spotify.com',
                'scdn.co',
            ]),
            'cookie_expiry' => __('Up to 1 year', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '<script>if(typeof window.RRZELegal === "object") { window.RRZELegal.unblockContentId("spotify"); }</script>',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 3,
            'status' => false,
            'static' => true,
        ],
        'vimeo' => [
            'id' => 'vimeo',
            'cookie_name' => 'vuid, player, flags, player_clearance, _cf_bm, _cfuvid, cf_clearance',
            'category' => 'external_media',
            'name' => __('Vimeo', 'rrze-legal'),
            'provider' => __('Vimeo.com, Inc., 330 West 34th Street, 5th Floor, New York, New York 10001, USA', 'rrze-legal'),
            'purpose' => __('Used to unblock Vimeo content.', 'rrze-legal'),
            'privacy_text_de' => 'Diese Website nutzt Plugins des Videoportals Vimeo. Anbieter ist Vimeo.com, Inc., 330 West 34th Street, 5th Floor, New York, New York 10001, USA.

Wenn Sie eine unserer mit einem Vimeo-Plugin ausgestatteten Seiten besuchen und den Inhalt aktivieren, wird eine Verbindung zu den Servern von Vimeo hergestellt. Dabei wird dem Vimeo-Server mitgeteilt, welche unserer Seiten Sie besucht haben. Zudem kann Vimeo Ihre IP-Adresse verarbeiten. Dies gilt auch dann, wenn Sie nicht bei Vimeo eingeloggt sind oder keinen Vimeo-Account besitzen. Die von Vimeo erfassten Informationen können an Vimeo-Server in den USA übermittelt werden.

Wenn Sie in Ihrem Vimeo-Konto eingeloggt sind, ermöglichen Sie Vimeo, Ihr Surfverhalten direkt Ihrem persönlichen Profil zuzuordnen. Dies können Sie verhindern, indem Sie sich aus Ihrem Vimeo-Konto ausloggen.

Die Nutzung von Vimeo erfolgt auf Grundlage Ihrer Einwilligung nach Art. 6 Abs. 1 lit. a DSGVO. Die Einwilligung ist jederzeit widerrufbar.',
            'privacy_text_en' => 'This website uses plugins from the Vimeo video portal. The provider is Vimeo.com, Inc., 330 West 34th Street, 5th Floor, New York, New York 10001, USA.

If you visit one of our pages that contains a Vimeo plugin and activate the content, a connection to Vimeo servers is established. The Vimeo server is informed which of our pages you have visited. Vimeo may also process your IP address. This applies even if you are not logged in to Vimeo or do not have a Vimeo account. The information collected by Vimeo may be transmitted to Vimeo servers in the USA.

If you are logged in to your Vimeo account, Vimeo may associate your browsing behavior directly with your personal profile. You can prevent this by logging out of your Vimeo account.

The use of Vimeo is based on your consent pursuant to Art. 6 (1) (a) GDPR. Consent can be withdrawn at any time.',
            'privacy_policy_url' => 'https://vimeo.com/privacy',
            'hosts' => implode(PHP_EOL, [
                'player.vimeo.com',
                'vimeo.com',
                'i.vimeocdn.com',
            ]),
            'cookie_expiry' => __('Up to 2 years', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '<script>if(typeof window.RRZELegal === "object") { window.RRZELegal.unblockContentId("vimeo"); }</script>',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 4,
            'plugin_slug' => 'rrze-video/rrze-video.php',
            'status' => consent()->isServiceProviderActive('vimeo'),
            'static' => true,
        ],
        'gstatic' => [
            'id' => 'gstatic',
            'cookie_name' => 'NID, AEC',
            'category' => 'external_media',
            'name' => __('Gstatic', 'rrze-legal'),
            'provider' => __('Google Ireland Limited, Gordon House, Barrow Street, Dublin 4, Ireland', 'rrze-legal'),
            'purpose' => __('Used to load static Google resources.', 'rrze-legal'),
            'privacy_text_de' => 'Unsere Website kann den Dienst Gstatic verwenden. Anbieter ist Google Ireland Limited, Gordon House, Barrow Street, Dublin 4, Irland.

Gstatic ist ein von Google verwendeter Dienst zum Abrufen statischer Inhalte, um benötigte Ressourcen bereitzustellen und die Bandbreitennutzung zu reduzieren. Der Dienst kann insbesondere beim Laden von Hintergrunddaten für Google-Dienste wie Google Fonts oder Google Maps verwendet werden.

Wenn Gstatic-Ressourcen aktiviert und geladen werden, kann eine Verbindung zu Servern von Google hergestellt werden. Dabei können personenbezogene Daten wie Ihre IP-Adresse und technische Informationen zum Browser an Google übertragen werden. Eine Übermittlung personenbezogener Daten an Google LLC in den USA kann nicht ausgeschlossen werden. Für Übermittlungen in die USA kann ein Angemessenheitsbeschluss der EU-Kommission zum EU-US Data Privacy Framework herangezogen werden, soweit der Anbieter entsprechend zertifiziert ist.

Die Nutzung erfolgt auf Grundlage Ihrer Einwilligung nach Art. 6 Abs. 1 lit. a DSGVO. Die Einwilligung ist jederzeit widerrufbar.',
            'privacy_text_en' => 'Our website may use the Gstatic service. The provider is Google Ireland Limited, Gordon House, Barrow Street, Dublin 4, Ireland.

Gstatic is a service used by Google to retrieve static content in order to provide required resources and reduce bandwidth usage. The service may be used in particular when loading background data for Google services such as Google Fonts or Google Maps.

If Gstatic resources are activated and loaded, a connection to Google servers may be established. Personal data such as your IP address and technical browser information may be transmitted to Google. A transfer of personal data to Google LLC in the USA cannot be ruled out. Transfers to the USA may be based on the European Commission\'s adequacy decision for the EU-US Data Privacy Framework, provided that the provider is certified accordingly.

The use is based on your consent pursuant to Art. 6 (1) (a) GDPR. Consent can be withdrawn at any time.',
            'privacy_policy_url' => 'https://policies.google.com/privacy',
            'hosts' => implode(PHP_EOL, [
                'gstatic.com',
                'google.com',
                'googleapis.com',
            ]),
            'cookie_expiry' => __('Up to 6 months', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '<script>if(typeof window.RRZELegal === "object") { window.RRZELegal.unblockContentId("gstatic"); }</script>',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 5,
            'status' => false,
            'static' => true,
        ],
        'sentry' => [
            'id' => 'sentry',
            'cookie_name' => '',
            'category' => 'external_media',
            'name' => __('Sentry', 'rrze-legal'),
            'provider' => __('Functional Software, Inc. dba Sentry, 45 Fremont Street, 8th Floor, San Francisco, CA 94105, USA', 'rrze-legal'),
            'purpose' => __('Used for error reporting and application monitoring.', 'rrze-legal'),
            'privacy_text_de' => 'Unsere Website kann Sentry für die Fehlerberichterstattung und Anwendungsüberwachung verwenden. Anbieter ist Functional Software, Inc. dba Sentry, 45 Fremont Street, 8th Floor, San Francisco, CA 94105, USA.

Sentry bietet eine Lösung zur Anwendungsüberwachung, mit der Fehler, Bugs und andere Leistungsprobleme erkannt, überwacht und ausgewertet werden können. Bei aktiver Fehlerberichterstattung können Daten an Sentry übertragen werden. Hierbei kann es sich unter anderem um die IP-Adresse, Browser-Fehlerprotokolle, die Referrer-URL, Ortsinformationen sowie Informationen über den verwendeten Browser und das verwendete Endgerät handeln.

Nach Angaben von Sentry setzt das SDK selbst üblicherweise keine Cookies im Browser der Endnutzer. Gleichwohl können bei der Übertragung von Fehler- und Diagnosedaten personenbezogene Daten verarbeitet werden.

Die Nutzung erfolgt auf Grundlage Ihrer Einwilligung nach Art. 6 Abs. 1 lit. a DSGVO. Die Einwilligung ist jederzeit widerrufbar.',
            'privacy_text_en' => 'Our website may use Sentry for error reporting and application monitoring. The provider is Functional Software, Inc. dba Sentry, 45 Fremont Street, 8th Floor, San Francisco, CA 94105, USA.

Sentry provides an application monitoring solution that can be used to detect, monitor and evaluate errors, bugs and other performance issues. If error reporting is active, data may be transmitted to Sentry. This may include the IP address, browser error logs, the referrer URL, location information and information about the browser and device used.

According to Sentry, the SDK itself usually does not set cookies in the browsers of end users. Nevertheless, personal data may be processed when error and diagnostic data are transmitted.

The use is based on your consent pursuant to Art. 6 (1) (a) GDPR. Consent can be withdrawn at any time.',
            'privacy_policy_url' => 'https://sentry.io/privacy/',
            'hosts' => implode(PHP_EOL, [
                'sentry.io',
                'ingest.sentry.io',
                'ingest.de.sentry.io',
            ]),
            'cookie_expiry' => __('No cookies', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '<script>if(typeof window.RRZELegal === "object") { window.RRZELegal.unblockContentId("sentry"); }</script>',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 6,
            'status' => false,
            'static' => true,
        ],
        'slideshare' => [
            'id' => 'slideshare',
            'cookie_name' => '__utma, _ga, OptanonConsent',
            'category' => 'external_media',
            'name' => __('Slideshare', 'rrze-legal'),
            'provider' => __('Scribd, Inc., 460 Bryant St, 100, San Francisco, CA 94107-2594 USA', 'rrze-legal'),
            'purpose' => __('Used to unblock Slideshare content.', 'rrze-legal'),
            'privacy_text_de' => 'Diese Website kann Präsentationen über das Online-Portal Slideshare einbinden. Betreiber ist Scribd, Inc. Wenn eine Seite mit eingebundenen Slideshare-Inhalten aufgerufen und der Inhalt aktiviert wird, kann eine direkte Verbindung zu Servern des Anbieters hergestellt werden.

Dabei können Nutzungsdaten, Geräteinformationen, IP-Adressen und Cookie-Informationen verarbeitet werden. Zweck und Umfang der Datenverarbeitung sowie Ihre Rechte und Einstellungsmöglichkeiten ergeben sich aus der Datenschutzerklärung des Anbieters.

Die Nutzung erfolgt auf Grundlage Ihrer Einwilligung nach Art. 6 Abs. 1 lit. a DSGVO. Die Einwilligung ist jederzeit widerrufbar.',
            'privacy_text_en' => 'This website may embed presentations via the Slideshare online portal. The provider is Scribd, Inc. If a page with embedded Slideshare content is accessed and the content is activated, a direct connection to the provider\'s servers may be established.

Usage data, device information, IP addresses and cookie information may be processed. The purpose and scope of data processing as well as your rights and settings options are described in the provider\'s privacy policy.

The use is based on your consent pursuant to Art. 6 (1) (a) GDPR. Consent can be withdrawn at any time.',
            'privacy_policy_url' => 'https://support.scribd.com/hc/en-us/articles/210129366-Global-Privacy-Policy',
            'hosts' => implode(PHP_EOL, [
                'slideshare.net',
                'scribd.com',
                'slidesharecdn.com',
            ]),
            'cookie_expiry' => __('Up to 2 years', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '<script>if(typeof window.RRZELegal === "object") { window.RRZELegal.unblockContentId("slideshare"); }</script>',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 7,
            'plugin_slug' => 'fau-oembed/fau-oembed.php',
            'status' => consent()->isServiceProviderActive('slideshare'),
            'static' => true,
        ],
        'brmediathek' => [
            'id' => 'brmediathek',
            'cookie_name' => 'atid, atidvisitor, atuserid, atoptedout, idrxvr, atidx',
            'category' => 'external_media',
            'name' => __('BR Mediathek', 'rrze-legal'),
            'provider' => __('Bayerischer Rundfunk, Rundfunkplatz 1, 80335 Munich, Germany', 'rrze-legal'),
            'purpose' => __('Used to unblock BR content.', 'rrze-legal'),
            'privacy_text_de' => 'Diese Website kann Inhalte der BR Mediathek einbinden. Wenn solche Inhalte aktiviert werden, kann eine Verbindung zu Servern des Bayerischen Rundfunks hergestellt werden. Dabei können personenbezogene Daten wie IP-Adresse, Browserinformationen und Informationen zur aufgerufenen Seite verarbeitet werden.

Die Nutzung erfolgt auf Grundlage Ihrer Einwilligung nach Art. 6 Abs. 1 lit. a DSGVO. Die Einwilligung ist jederzeit widerrufbar.',
            'privacy_text_en' => 'This website may embed content from BR Mediathek. If such content is activated, a connection to servers of Bayerischer Rundfunk may be established. Personal data such as IP address, browser information and information about the page accessed may be processed.

The use is based on your consent pursuant to Art. 6 (1) (a) GDPR. Consent can be withdrawn at any time.',
            'privacy_policy_url' => 'https://www.br.de/unternehmen/service/impressum/impressum-datenschutzerklaerung-unternehmen-v2-100.html',
            'hosts' => implode(PHP_EOL, [
                'br.de',
                'ardmediathek.de',
                'ati-host.net',
                'xiti.com',
            ]),
            'cookie_expiry' => __('Up to 180 days', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '<script>if(typeof window.RRZELegal === "object") { window.RRZELegal.unblockContentId("brmediathek"); }</script>',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 8,
            'plugin_slug' => 'rrze-video/rrze-video.php',
            'status' => consent()->isServiceProviderActive('brmediathek'),
            'static' => true,
        ],
        'ardmediathek' => [
            'id' => 'ardmediathek',
            'cookie_name' => 'atidvisitor, atuserid, atoptedout, idrxvr, atidx',
            'category' => 'external_media',
            'name' => __('ARD Mediathek', 'rrze-legal'),
            'provider' => __('Bayerischer Rundfunk, Rundfunkplatz 1, 80335 Munich, Germany', 'rrze-legal'),
            'purpose' => __('Used to unblock ARD content.', 'rrze-legal'),
            'privacy_text_de' => 'Diese Website kann Inhalte der ARD Mediathek einbinden. Wenn solche Inhalte aktiviert werden, kann eine Verbindung zu Servern des Anbieters hergestellt werden. Dabei können personenbezogene Daten wie IP-Adresse, Browserinformationen und Informationen zur aufgerufenen Seite verarbeitet werden.

Die Nutzung erfolgt auf Grundlage Ihrer Einwilligung nach Art. 6 Abs. 1 lit. a DSGVO. Die Einwilligung ist jederzeit widerrufbar.',
            'privacy_text_en' => 'This website may embed content from ARD Mediathek. If such content is activated, a connection to the provider\'s servers may be established. Personal data such as IP address, browser information and information about the page accessed may be processed.

The use is based on your consent pursuant to Art. 6 (1) (a) GDPR. Consent can be withdrawn at any time.',
            'privacy_policy_url' => 'https://www.ardmediathek.de/datenschutz',
            'hosts' => implode(PHP_EOL, [
                'ard.de',
                'ardmediathek.de',
                'ati-host.net',
                'xiti.com',
            ]),
            'cookie_expiry' => __('Up to 12 months', 'rrze-legal'),
            'enqueued_script_handles' => '',
            'block_enqueued_script' => false,
            'prioritize' => false,
            'async_opt_out_code' => false,
            'opt_in_js' => '<script>if(typeof window.RRZELegal === "object") { window.RRZELegal.unblockContentId("ardmediathek"); }</script>',
            'opt_out_js' => '',
            'fallback_js' => '',
            'position' => 9,
            'plugin_slug' => 'rrze-video/rrze-video.php',
            'status' => consent()->isServiceProviderActive('ardmediathek'),
            'static' => true,
        ],
    ],
];
