<?php

namespace Hamlet\Http\Message\Spec\Traits;

trait DataProviderTrait
{
    public function invalid_protocol_versions()
    {
        return [
            ['a'],
            [null],
            ['1.a'],
            [1.],
            ['2.'],
            ['\0'],
            ['x1.5'],
            [false],
            [new \stdClass()],
            [function () {}],
            [['2.0']],
            ['1.1 enhanced']
        ];
    }

    public function valid_protocol_versions()
    {
        return [
            ['0.9'],
            ['1.0'],
            ['1.1'],
            ['2.0'],
            ['2'],
            ['3']
        ];
    }

    public function host_header_variations()
    {
        return [
            'lowercase'         => ['host'],
            'mixed-4'           => ['hosT'],
            'mixed-3-4'         => ['hoST'],
            'reverse-titlecase' => ['hOST'],
            'uppercase'         => ['HOST'],
            'mixed-1-2-3'       => ['HOSt'],
            'mixed-1-2'         => ['HOst'],
            'titlecase'         => ['Host'],
            'mixed-1-4'         => ['HosT'],
            'mixed-1-2-4'       => ['HOsT'],
            'mixed-1-3-4'       => ['HoST'],
            'mixed-1-3'         => ['HoSt'],
            'mixed-2-3'         => ['hOSt'],
            'mixed-2-4'         => ['hOsT'],
            'mixed-2'           => ['hOst'],
            'mixed-3'           => ['hoSt']
        ];
    }

    public function valid_header_names()
    {
        return [
            ['Access-Control-Allow-Credentials'],
            ['Access-Control-Allow-Headers'],
            ['Access-Control-Allow-Methods'],
            ['Access-Control-Allow-Origin'],
            ['Access-Control-Expose-Headers'],
            ['Access-Control-Max-Age'],
            ['Accept-Ranges'],
            ['Age'],
            ['Allow'],
            ['Alternate-Protocol'],
            ['Cache-Control'],
            ['Client-Date'],
            ['Client-Peer'],
            ['Client-Response-Num'],
            ['Connection'],
            ['Content-Disposition'],
            ['Content-Encoding'],
            ['Content-Language'],
            ['Content-Length'],
            ['Content-Location'],
            ['Content-MD5'],
            ['Content-Range'],
            ['Content-Security-Policy'],
            ['Content-Security-Policy-Report-Only'],
            ['Content-Type'],
            ['Date'],
            ['ETag'],
            ['Expires'],
            ['HTTP'],
            ['Keep-Alive'],
            ['Last-Modified'],
            ['Link'],
            ['Location'],
            ['P3P'],
            ['Pragma'],
            ['Proxy-Authenticate'],
            ['Proxy-Connection'],
            ['Refresh'],
            ['Retry-After'],
            ['Server'],
            ['Set-Cookie'],
            ['Status'],
            ['Strict-Transport-Security'],
            ['Timing-Allow-Origin'],
            ['Trailer'],
            ['Transfer-Encoding'],
            ['Upgrade'],
            ['Vary'],
            ['Via'],
            ['Warning'],
            ['WWW-Authenticate'],
            ['X-Content-Type-Options'],
            ['X-Frame-Options'],
            ['X-Permitted-Cross-Domain-Policies'],
            ['X-Pingback'],
            ['X-Powered-By'],
            ['X-Robots-Tag'],
            ['X-UA-Compatible'],
            ['X-XSS-Protection'],
        ];
    }

    public function invalid_header_names()
    {
        return [
            [233],
            [null],
            ['hey dude'],
            ['Location:'],
            ['This-is-a-cyrillic-о']
        ];
    }

    public function valid_header_values()
    {
        return [
            ['text/plain'],
            ['PHP 9.1'],
            ['text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8'],
            ['gzip, deflate, br'],
            ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36']
        ];
    }

    public function invalid_header_values()
    {
        return [
            ["package http\r\nH: 0 0\r\n\r\n"],
            [null],
            [new \stdClass()]
        ];
    }

    public function valid_request_targets()
    {
        return [
            'asterisk-form'         => ['*'],
            'authority-form'        => ['api.example.com'],
            'absolute-form'         => ['https://api.example.com/users'],
            'absolute-form-query'   => ['https://api.example.com/users?foo=bar'],
            'origin-form-path-only' => ['/users'],
            'origin-form'           => ['/users?id=foo'],
        ];
    }

    public function invalid_request_targets()
    {
        return [
            'with-space'   => ['foo bar baz'],
            'invalid-type' => [12],
            'null'         => [null],
            'object'       => [new \stdClass()]
        ];
    }

    public function uris_with_request_targets()
    {
        return [
            ['http://foo.com/baz?bar=bam', '/baz?bar=bam'],
            ['http://example.com', '/'],
            ['http://example.com#proceed', '/']
        ];
    }

    public function valid_request_methods()
    {
        return [
            'TRACE'          => ['TRACE'],
            'PROPFIND'       => ['PROPFIND'],
            'PROPPATCH'      => ['PROPPATCH'],
            'MKCOL'          => ['MKCOL'],
            'COPY'           => ['COPY'],
            'MOVE'           => ['MOVE'],
            'LOCK'           => ['LOCK'],
            'UNLOCK'         => ['UNLOCK']
        ];
    }

    public function invalid_request_methods()
    {
        return [
            'number'                     => [123],
            'contains-space'             => ['hey dude'],
            'contains-special-character' => ['POST!'],
            'contains-numbers'           => ['GET1'],
            'null'                       => [null]
        ];
    }

    public function valid_uris()
    {
        return [
            ['urn:path-rootless'],
            ['urn:path:with:colon'],
            ['urn:/path-absolute'],
            ['urn:/'],
            // only scheme with empty path
            ['urn:'],
            // only path
            ['/'],
            ['relative/'],
            ['0'],
            // same document reference
            [''],
            // network path without scheme
            ['//example.org'],
            ['//example.org/'],
            ['//example.org?q#h'],
            // only query
            ['?q'],
            ['?q=abc&foo=bar'],
            // only fragment
            ['#fragment'],
            // dot segments are not removed automatically
            ['./foo/../bar'],
        ];
    }

    public function invalid_uris()
    {
        return [
            // parse_url() requires the host component which makes sense for http(s)
            // but not when the scheme is not known or different. So '//' or '///' is
            // currently invalid as well but should not according to RFC 3986.
            ['http://'],
            ['urn://host:with:colon'], // host cannot contain ":"
            [null],
            [1],
            [new \stdClass()],
            // incorrect port numbers
            ['//example.com:0'],
            ['//example.com:10000000']
        ];
    }

    public function valid_uri_schemes()
    {
        return [
            ['aaa'],
            ['aaas'],
            ['about'],
            ['acap'],
            ['acct'],
            ['acr'],
            ['adiumxtra'],
            ['afp'],
            ['afs'],
            ['aim'],
            ['appdata'],
            ['apt'],
            ['attachment'],
            ['aw'],
            ['barion'],
            ['beshare'],
            ['bitcoin'],
            ['bitcoincash'],
            ['blob'],
            ['bolo'],
            ['browserext'],
            ['callto'],
            ['cap'],
            ['chrome'],
            ['chrome-extension'],
            ['cid'],
            ['coap'],
            ['coap+tcp'],
            ['coap+ws'],
            ['coaps'],
            ['coaps+tcp'],
            ['coaps+ws'],
            ['com-eventbrite-attendee'],
            ['content'],
            ['conti'],
            ['crid'],
            ['cvs'],
            ['data'],
            ['dav'],
            ['diaspora'],
            ['dict'],
            ['did'],
            ['dis'],
            ['dlna-playcontainer'],
            ['dlna-playsingle'],
            ['dns'],
            ['dntp'],
            ['dtn'],
            ['dvb'],
            ['ed2k'],
            ['elsi'],
            ['example'],
            ['facetime'],
            ['fax'],
            ['feed'],
            ['feedready'],
            ['file'],
            ['filesystem'],
            ['finger'],
            ['fish'],
            ['ftp'],
            ['geo'],
            ['gg'],
            ['git'],
            ['gizmoproject'],
            ['go'],
            ['gopher'],
            ['graph'],
            ['gtalk'],
            ['h323'],
            ['ham'],
            ['hcap'],
            ['hcp'],
            ['http'],
            ['https'],
            ['hxxp'],
            ['hxxps'],
            ['hydrazone'],
            ['iax'],
            ['icap'],
            ['icon'],
            ['im'],
            ['imap'],
            ['info'],
            ['iotdisco'],
            ['ipn'],
            ['ipp'],
            ['ipps'],
            ['irc'],
            ['irc6'],
            ['ircs'],
            ['iris'],
            ['iris.beep'],
            ['iris.lwz'],
            ['iris.xpc'],
            ['iris.xpcs'],
            ['isostore'],
            ['itms'],
            ['jabber'],
            ['jar'],
            ['jms'],
            ['keyparc'],
            ['lastfm'],
            ['ldap'],
            ['ldaps'],
            ['lvlt'],
            ['magnet'],
            ['mailserver'],
            ['mailto'],
            ['maps'],
            ['market'],
            ['message'],
            ['microsoft.windows.camera'],
            ['microsoft.windows.camera.multipicker'],
            ['microsoft.windows.camera.picker'],
            ['mid'],
            ['mms'],
            ['modem'],
            ['mongodb'],
            ['moz'],
            ['ms-access'],
            ['ms-browser-extension'],
            ['ms-drive-to'],
            ['ms-enrollment'],
            ['ms-excel'],
            ['ms-eyecontrolspeech'],
            ['ms-gamebarservices'],
            ['ms-gamingoverlay'],
            ['ms-getoffice'],
            ['ms-help'],
            ['ms-infopath'],
            ['ms-inputapp'],
            ['ms-lockscreencomponent-config'],
            ['ms-media-stream-id'],
            ['ms-mixedrealitycapture'],
            ['ms-officeapp'],
            ['ms-people'],
            ['ms-project'],
            ['ms-powerpoint'],
            ['ms-publisher'],
            ['ms-restoretabcompanion'],
            ['ms-screenclip'],
            ['ms-screensketch'],
            ['ms-search'],
            ['ms-search-repair'],
            ['ms-secondary-screen-controller'],
            ['ms-secondary-screen-setup'],
            ['ms-settings'],
            ['ms-settings-airplanemode'],
            ['ms-settings-bluetooth'],
            ['ms-settings-camera'],
            ['ms-settings-cellular'],
            ['ms-settings-cloudstorage'],
            ['ms-settings-connectabledevices'],
            ['ms-settings-displays-topology'],
            ['ms-settings-emailandaccounts'],
            ['ms-settings-language'],
            ['ms-settings-location'],
            ['ms-settings-lock'],
            ['ms-settings-nfctransactions'],
            ['ms-settings-notifications'],
            ['ms-settings-power'],
            ['ms-settings-privacy'],
            ['ms-settings-proximity'],
            ['ms-settings-screenrotation'],
            ['ms-settings-wifi'],
            ['ms-settings-workplace'],
            ['ms-spd'],
            ['ms-sttoverlay'],
            ['ms-transit-to'],
            ['ms-useractivityset'],
            ['ms-virtualtouchpad'],
            ['ms-visio'],
            ['ms-walk-to'],
            ['ms-whiteboard'],
            ['ms-whiteboard-cmd'],
            ['ms-word'],
            ['msnim'],
            ['msrp'],
            ['msrps'],
            ['mtqp'],
            ['mumble'],
            ['mupdate'],
            ['mvn'],
            ['news'],
            ['nfs'],
            ['ni'],
            ['nih'],
            ['nntp'],
            ['notes'],
            ['ocf'],
            ['oid'],
            ['onenote'],
            ['onenote-cmd'],
            ['opaquelocktoken'],
            ['openpgp4fpr'],
            ['pack'],
            ['palm'],
            ['paparazzi'],
            ['pkcs11'],
            ['platform'],
            ['pop'],
            ['pres'],
            ['prospero'],
            ['proxy'],
            ['pwid'],
            ['psyc'],
            ['qb'],
            ['query'],
            ['redis'],
            ['rediss'],
            ['reload'],
            ['res'],
            ['resource'],
            ['rmi'],
            ['rsync'],
            ['rtmfp'],
            ['rtmp'],
            ['rtsp'],
            ['rtsps'],
            ['rtspu'],
            ['secondlife'],
            ['service'],
            ['session'],
            ['sftp'],
            ['sgn'],
            ['shttp'],
            ['sieve'],
            ['simpleledger'],
            ['sip'],
            ['sips'],
            ['skype'],
            ['smb'],
            ['sms'],
            ['smtp'],
            ['snews'],
            ['snmp'],
            ['soap.beep'],
            ['soap.beeps'],
            ['soldat'],
            ['spiffe'],
            ['spotify'],
            ['ssh'],
            ['steam'],
            ['stun'],
            ['stuns'],
            ['submit'],
            ['svn'],
            ['tag'],
            ['teamspeak'],
            ['tel'],
            ['teliaeid'],
            ['telnet'],
            ['tftp'],
            ['things'],
            ['thismessage'],
            ['tip'],
            ['tn3270'],
            ['tool'],
            ['turn'],
            ['turns'],
            ['tv'],
            ['udp'],
            ['unreal'],
            ['urn'],
            ['ut2004'],
            ['v-event'],
            ['vemmi'],
            ['ventrilo'],
            ['videotex'],
            ['vnc'],
            ['view-source'],
            ['wais'],
            ['webcal'],
            ['wpid'],
            ['ws'],
            ['wss'],
            ['wtai'],
            ['wyciwyg'],
            ['xcon'],
            ['xcon-userid'],
            ['xfire'],
            ['xmlrpc.beep'],
            ['xmlrpc.beeps'],
            ['xmpp'],
            ['xri'],
            ['ymsgr'],
            ['z39.50'],
            ['z39.50r'],
            ['z39.50s']
        ];
    }

    public function invalid_uri_schemes()
    {
        return [
            [0],
            [null],
            [7.4],
            [true],
            [new \stdClass()],
            [function () {}],
            [':80'],
            ['80 but not always']
        ];
    }

    public function invalid_uri_user_infos()
    {
        return [
            [0, null],
            [null, null],
            [true, null],
            [new \stdClass(), null],
            ['user', true],
            ['user', new \stdClass()]
        ];
    }

    public function invalid_uri_hosts()
    {
        return [
            [0],
            [null],
            [7.4],
            [true],
            [new \stdClass()],
            [['example.com']],
            [function() {}]
        ];
    }

    public function invalid_uri_ports()
    {
        return [
            [0],
            [-2],
            [PHP_INT_MAX],
            [PHP_INT_MIN],
            [0xffff + 1],
            [rand(0xffff + 1, 0xfffff)],
            ['100'],
            [7.4],
            [true],
            [new \stdClass()],
            [':80'],
            ['80 but not always']
        ];
    }

    public function invalid_uri_paths()
    {
        return [
            [null],
            [7.4],
            [true],
            [new \stdClass()]
        ];
    }

    public function invalid_uri_queries()
    {
        return [
            [null],
            [true],
            [new \stdClass()]
        ];
    }

    public function invalid_uri_fragments()
    {
        return [
            [[]],
            [['/path']],
            [null],
            [true],
            [new \stdClass()],
            [[new \stdClass()]],
            [function () {}],
            [[function () {}]]
        ];
    }

    public function uri_components()
    {
        $unreserved = 'a-zA-Z0-9.-_~!$&\'()*+,;=:@';
        return [
            // Percent encode spaces
            ['/pa th?q=va lue#frag ment', '/pa%20th', 'q=va%20lue', 'frag%20ment', '/pa%20th?q=va%20lue#frag%20ment'],
            // Percent encode multibyte
            ['/€?€#€', '/%E2%82%AC', '%E2%82%AC', '%E2%82%AC', '/%E2%82%AC?%E2%82%AC#%E2%82%AC'],
            // Don't encode something that's already encoded
            ['/pa%20th?q=va%20lue#frag%20ment', '/pa%20th', 'q=va%20lue', 'frag%20ment', '/pa%20th?q=va%20lue#frag%20ment'],
            // Percent encode invalid percent encodings
            ['/pa%2-th?q=va%2-lue#frag%2-ment', '/pa%252-th', 'q=va%252-lue', 'frag%252-ment', '/pa%252-th?q=va%252-lue#frag%252-ment'],
            // Don't encode path segments
            ['/pa/th//two?q=va/lue#frag/ment', '/pa/th//two', 'q=va/lue', 'frag/ment', '/pa/th//two?q=va/lue#frag/ment'],
            // Don't encode unreserved chars or sub-delimiters
            ["/$unreserved?$unreserved#$unreserved", "/$unreserved", $unreserved, $unreserved, "/$unreserved?$unreserved#$unreserved"],
            // Encoded unreserved chars are not decoded
            ['/p%61th?q=v%61lue#fr%61gment', '/p%61th', 'q=v%61lue', 'fr%61gment', '/p%61th?q=v%61lue#fr%61gment'],
        ];
    }

    public function valid_query_params()
    {
        return [
            [[]],
            [['a' => '1']],
            [['a' => ['1', '2', '3']]]
        ];
    }

    public function valid_cookie_params()
    {
        return [
            [[]],
            [['a' => '1']],
            [['a' => 'value']]
        ];
    }

    public function valid_uploaded_files()
    {
        return [
            [[]]
        ];
    }

    public function valid_parsed_bodies()
    {
        return [
            [null],
            [[]],
            [new \stdClass()]
        ];
    }

    public function valid_attribute_names_and_values()
    {
        return [
            ['name', null],
            ['name', 1],
            ['name', [1, 2, 3]],
            ['name', false],
            ['name', 1.1],
            ['name', 'string'],
            ['name', new \stdClass()],
            ['another name !', function () {}]
        ];
    }

    public function invalid_query_params()
    {
        return [
            [null],
            [1],
            [1.1],
            [false],
            ['value'],
            [new \stdClass()],
            [[1 => new \stdClass()]]
        ];
    }

    public function invalid_cookie_params()
    {
        return [
            [null],
            [1],
            [1.1],
            [false],
            ['value'],
            [new \stdClass()],
            [[1 => new \stdClass()]]
        ];
    }

    public function invalid_uploaded_files()
    {
        return [
            [null],
            [[null]],
            ['file'],
            [['file']],
            [1],
            [[1]],
            [1.0],
            [[1.0]],
            [false],
            [[false]],
            [new \stdClass()],
            [[new \stdClass()]],
            [function () {}],
            [[function () {}]],
            [[99 => new \stdClass()]]
        ];
    }

    public function invalid_body()
    {
        return [
            'null'      => [null],
            'true'      => [true],
            'false'     => [false],
            'int'       => [1],
            'float'     => [1.1],
            'array'     => [['BODY']],
            'stdClass'  => [(object)['body' => 'BODY']]
        ];
    }

    public function invalid_parsed_bodies()
    {
        return [
            [1],
            [1.1],
            [false],
            ['value']
        ];
    }

    public function invalid_attribute_names_and_values()
    {
        return [
            [null, 1],
            [1, 2],
            [1.1, 'test'],
            [false, null],
            [new \stdClass(), 1],
            [function () {}, 'value']
        ];
    }

    public function invalid_resources()
    {
        $name = tempnam(sys_get_temp_dir(), 'psr-7');
        return [
            'null'                => [ null ],
            'false'               => [ false ],
            'true'                => [ true ],
            'int'                 => [ 1 ],
            'float'               => [ 1.1 ],
            'string-non-resource' => [ 'foo-bar-baz' ],
            'array'               => [ [ fopen($name, 'r+') ] ],
            'object'              => [ (object) [ 'resource' => fopen($name, 'r+') ] ],
        ];
    }

    public function all_modes()
    {
        return [
            ['a',   false,  true],
            ['a+',   true,  true],
            ['a+b',  true,  true],
            ['ab',  false,  true],
            ['c',   false,  true],
            ['c+',   true,  true],
            ['c+b',  true,  true],
            ['c+t',  true,  true],
            ['cb',  false,  true],
            ['r',    true, false],
            ['r+',   true,  true],
            ['r+b',  true,  true],
            ['r+t',  true,  true],
            ['rb',   true, false],
            ['rt',   true, false],
            ['rw',   true,  true],
            ['w',   false,  true],
            ['w+',   true,  true],
            ['w+b',  true,  true],
            ['w+t',  true,  true],
            ['wb',  false,  true],
            ['x',   false,  true],
            ['x+',   true,  true],
            ['x+b',  true,  true],
            ['x+t',  true,  true],
            ['xb',  false,  true],
        ];
    }

    public function non_readable_modes()
    {
        foreach ($this->all_modes() as list($mode, $readable, $writable)) {
            if (!$readable) {
                yield [$mode];
            }
        }
    }

    public function non_writable_modes()
    {
        foreach ($this->all_modes() as list($mode, $readable, $writable)) {
            if (!$writable) {
                yield [$mode];
            }
        }
    }

    public function invalid_status_codes()
    {
        return [
            'true'     => [true],
            'false'    => [false],
            'array'    => [[200]],
            'object'   => [(object)['statusCode' => 200]],
            'too-low'  => [99],
            'float'    => [400.5],
            'too-high' => [600],
            'null'     => [null],
            'string'   => ['foo'],
        ];
    }

    public function invalid_reason_phrases()
    {
        return [
            'true'    => [true],
            'false'   => [false],
            'array'   => [[200]],
            'object'  => [(object)['reasonPhrase' => 'Ok']],
            'integer' => [99],
            'float'   => [400.5],
            'null'    => [null],
            'lambda'  => [function () {}]
        ];
    }

    public function headers_with_injection_vectors()
    {
        return [
            'name-with-cr'           => ["X-Foo\r-Bar", 'value'],
            'name-with-lf'           => ["X-Foo\n-Bar", 'value'],
            'name-with-crlf'         => ["X-Foo\r\n-Bar", 'value'],
            'name-with-2crlf'        => ["X-Foo\r\n\r\n-Bar", 'value'],
            'value-with-cr'          => ['X-Foo-Bar', "value\rinjection"],
            'value-with-lf'          => ['X-Foo-Bar', "value\ninjection"],
            'value-with-crlf'        => ['X-Foo-Bar', "value\r\ninjection"],
            'value-with-2crlf'       => ['X-Foo-Bar', "value\r\n\r\ninjection"],
            'array-value-with-cr'    => ['X-Foo-Bar', ["value\rinjection"]],
            'array-value-with-lf'    => ['X-Foo-Bar', ["value\ninjection"]],
            'array-value-with-crlf'  => ['X-Foo-Bar', ["value\r\ninjection"]],
            'array-value-with-2crlf' => ['X-Foo-Bar', ["value\r\n\r\ninjection"]],
        ];
    }

    public function invalid_streams()
    {
        return [
            'null'   => [null],
            'true'   => [true],
            'false'  => [false],
            'int'    => [1],
            'float'  => [1.1],
            'array'  => [['filename']],
            'object' => [(object)['filename']],
            'lambda' => [function () {}]
        ];
    }

    public function invalid_target_paths()
    {
        return [
            'null'   => [null],
            'true'   => [true],
            'false'  => [false],
            'int'    => [1],
            'float'  => [1.1],
            'empty'  => [''],
            'array'  => [['filename']],
            'object' => [(object)['filename']],
            'lambda' => [function () {}]
        ];
    }

    public function invalid_file_sizes()
    {
        return [
            'null'   => [null],
            'float'  => [1.1],
            'array'  => [[1]],
            'object' => [(object)[1]],
        ];
    }

    public function invalid_file_names()
    {
        return [
            'true'   => [true],
            'false'  => [false],
            'int'    => [1],
            'float'  => [1.1],
            'array'  => [['string']],
            'object' => [(object)['string']],
            'lambda' => [function () {}]
        ];
    }

    public function invalid_media_types()
    {
        return [
            'true'   => [true],
            'false'  => [false],
            'int'    => [1],
            'float'  => [1.1],
            'array'  => [['string']],
            'object' => [(object)['string']],
            'lambda' => [function () {}]
        ];
    }

    public function invalid_file_upload_error_statuses()
    {
        return [
            [-1],
            [74],
            [10000],
            [PHP_INT_MIN],
            [PHP_INT_MAX],
            [NAN]
        ];
    }

    public function file_upload_error_codes()
    {
        return [
            [\UPLOAD_ERR_INI_SIZE],
            [\UPLOAD_ERR_FORM_SIZE],
            [\UPLOAD_ERR_PARTIAL],
            [\UPLOAD_ERR_NO_FILE],
            [\UPLOAD_ERR_NO_TMP_DIR],
            [\UPLOAD_ERR_CANT_WRITE],
            [\UPLOAD_ERR_EXTENSION],
        ];
    }
}
