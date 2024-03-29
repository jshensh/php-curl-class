<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <title>Api Debugger</title>
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <link href="https://cdn.jsdelivr.net/npm/codemirror@5.58.3/lib/codemirror.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/codemirror@5.58.3/lib/codemirror.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/codemirror@5.58.3/mode/javascript/javascript.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/codemirror@5.58.3/addon/scroll/simplescrollbars.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/codemirror@5.58.3/addon/scroll/simplescrollbars.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js" integrity="sha256-AFAYEOkzB6iIKnTYZOdUf9FFje6lOTYdwRJKwTN5mks=" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" integrity="sha256-FdatTf20PQr/rWg+cAKfl6j4/IY3oohFAJ7gVC3M34E=" crossorigin="anonymous">
    </head>

    <body>
        <div class="contianer" style="margin: 20px;">
            <div class="row">
                <div class="col-md-4 col-lg-3" id="leftPanel">
                    <form onsubmit="loginApi(this); return false;">
                        <div class="form-group">
                            <label for="loginUrl">Login URL</label>
                            <input type="text" class="form-control" id="loginUrl" placeholder="https://" value="{{ $loginUrl }}">
                        </div>
@foreach ($loginForm as $dom)
    @switch ($dom['type'])
        @case ('hidden')
                        <input type="{{ $dom['type'] }}" name="{{ $dom['key'] }}" placeholder="{{ $dom['placeholder'] }}" value="{{ $dom['value'] }}" />
            @break

        @case ('html')
                        {!! $dom['html'] !!}
            @break

        @default
                        <div class="form-group">
                            <label for="{{ $dom['key'] }}">{{ $dom['label'] }}</label>
                            <input type="{{ $dom['type'] }}" class="form-control" name="{{ $dom['key'] }}" id="{{ $dom['key'] }}" placeholder="{{ $dom['placeholder'] }}" value="{{ $dom['value'] }}" />
                        </div>
    @endswitch
@endforeach
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Flush Token</button>
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" id="token" placeholder="Authorization" rows="7" readonly="readonly"></textarea>
                        </div>
                    </form>
                    <hr />
                    <form class="form-horizontal">
                        <div class="form-group">
                            <label for="apiUrl" class="col-sm-2 control-label">Api List</label>
                            <div class="col-sm-10">
                                <select class="form-control" id="apiList" style="width: 100%;"></select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="apiUrl" class="col-sm-2 control-label">Api URL</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="apiUrl" placeholder="https://">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="method" class="col-sm-2 control-label">Method</label>
                            <div class="col-sm-10">
                                <select class="form-control" id="method" style="width: 100%;">
                                    <option value="GET">GET</option>
                                    <option value="POST" selected="selected">POST</option>
                                    <option value="PATCH">PATCH</option>
                                    <option value="PUT">PUT</option>
                                    <option value="DELETE">DELETE</option>
                                    <option value="OPTIONS">OPTIONS</option>
                                </select>
                            </div>
                        </div>
                    </form>
                    <p><label><input type="checkbox" id="isDownloadFile" /> For download file</label></p>
                    <label>Headers</label>
                    <form class="form-horizontal" id="headersContainer">
                        <div class="form-group" id="header_content_type_row">
                            <label for="header_content_type_value" class="col-sm-3 control-label" style="font-weight: 400;">Content-Type</label>
                            <div class="col-sm-9">
                                <select class="form-control" id="header_content_type_value" name="Content-Type" type="text" aria-describedby="header_content_type" style="width: 100%;">
                                    <option value="" selected="selected">Let the browser decide</option>
                                    <option value="application/x-www-form-urlencoded">application/x-www-form-urlencoded</option>
                                    <option value="application/json">application/json</option>
                                    <option value="text/xml">text/xml</option>
                                    <option value="application/octet-stream">application/octet-stream</option>
                                </select>
                            </div>
                        </div>
                    </form>
                    <label>Params</label>
                    <form class="form-horizontal" id="paramsContainer" onsubmit="$('#buttonForm').submit(); return false;">
                        <button type="submit" style="display: none;"></button>
                    </form>

                    <div id="stringContainer" style="border: 1px solid #ddd;" class="form-group">
                        <textarea class="form-control" id="stringToPost" placeholder="JSON / XML / URL Encoded String Here" rows="10"></textarea>
                    </div>

                    <form class="form-horizontal" id="buttonForm" onsubmit="requestApi(this); return false;">
                        <button type="button" class="btn btn-default" style="margin-bottom: 15px;" id="addHeaderButton">Add Header</button>
                        <button type="button" class="btn btn-default" style="margin-bottom: 15px;" id="addParamButton">Add Param</button>
                        <button type="button" class="btn btn-default" style="margin-bottom: 15px;" id="addFileButton">Add File</button>
                        <button type="submit" class="btn btn-primary" style="margin-bottom: 15px;">Submit</button>
                    </form>
                </div>
                <div class="fixedDivContainer">
                    <div class="col-md-8 col-lg-9 col-md-push-4 col-lg-push-3 fixedDiv">
                        <label>Response Headers:</label>
                        <div style="border: 1px solid #ddd;">
                            <textarea id="responseHeader" style="width: 100%;" class="file-editor-textarea js-code-textarea"></textarea>
                        </div>
                        <label style="margin-top: 5px;">Response Body:</label>
                        <div style="border: 1px solid #ddd;">
                            <textarea id="responseBody" style="width: 100%;" class="file-editor-textarea js-code-textarea"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="footer">
            <div class="container">
                <p style="height: 50px; line-height: 50px; margin: 0;">Copyright © 2021 <a href="https://github.com/jshensh/phpCurlClass" target="_blank">jshensh</a>. All rights reserved.</p>
            </div>
        </footer>
    </body>

    <style>
        pre {
          font: normal 10pt Consolas, Monaco, monospace;
        }

        html {
            position: relative;
            min-height: 100%;
        }

        body {
            /* Margin bottom by footer height */
            margin-bottom: 60px;
        }
        
        label {
            white-space: nowrap;
        }

        .fixedDivContainer:after {
            content: '';
            display: table;
            clear: both;
        }

        .fixedDivContainer {
            *zoom: 1;
        }

        @media (min-width: 992px) {
            .fixedDiv {
                position: fixed;
            }
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            /* Set the fixed height of the footer here */
            height: 50px;
            background-color: #f5f5f5;
            z-index: 999;
        }

        .CodeMirror {
            height: 150px;
        }

        /*!
         * GitHub Light v0.4.2
         * Copyright (c) 2012 - 2017 GitHub, Inc.
         * Licensed under MIT (https://github.com/primer/github-syntax-theme-generator/blob/master/LICENSE)
         */
        .cm-s-github-light.CodeMirror {
            background: #fff;
            color: #24292e
        }

        .cm-s-github-light .CodeMirror-gutters {
            background: #fff;
            border-right-width: 0
        }

        .cm-s-github-light .CodeMirror-guttermarker {
            color: white
        }

        .cm-s-github-light .CodeMirror-guttermarker-subtle {
            color: #d0d0d0
        }

        .cm-s-github-light .CodeMirror-linenumber {
            color: #959da5;
            padding: 0 16px 0 16px
        }

        .cm-s-github-light .CodeMirror-cursor {
            border-left: 1px solid #24292e
        }

        .cm-s-github-light div.CodeMirror-selected,.cm-s-github-light .CodeMirror-line::-moz-selection,.cm-s-github-light .CodeMirror-line>span::-moz-selection,.cm-s-github-light .CodeMirror-line>span>span::-moz-selection {
            background: #c8c8fa
        }

        .cm-s-github-light div.CodeMirror-selected,.cm-s-github-light .CodeMirror-line::selection,.cm-s-github-light .CodeMirror-line>span::selection,.cm-s-github-light .CodeMirror-line>span>span::selection,.cm-s-github-light .CodeMirror-line::-moz-selection,.cm-s-github-light .CodeMirror-line>span::-moz-selection,.cm-s-github-light .CodeMirror-line>span>span::-moz-selection {
            background: #c8c8fa
        }

        .cm-s-github-light .CodeMirror-activeline-background {
            background: #fafbfc
        }

        .cm-s-github-light .CodeMirror-matchingbracket {
            text-decoration: underline;
            color: #24292e !important
        }

        .cm-s-github-light .CodeMirror-lines {
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
            font-size: 12px;
            background: #fff;
            line-height: 1.5
        }

        .cm-s-github-light .cm-comment {
            color: #6a737d
        }

        .cm-s-github-light .cm-constant {
            color: #005cc5
        }

        .cm-s-github-light .cm-entity {
            font-weight: normal;
            font-style: normal;
            text-decoration: none;
            color: #6f42c1
        }

        .cm-s-github-light .cm-keyword {
            font-weight: normal;
            font-style: normal;
            text-decoration: none;
            color: #d73a49
        }

        .cm-s-github-light .cm-storage {
            color: #d73a49
        }

        .cm-s-github-light .cm-string {
            font-weight: normal;
            font-style: normal;
            text-decoration: none;
            color: #032f62
        }

        .cm-s-github-light .cm-support {
            font-weight: normal;
            font-style: normal;
            text-decoration: none;
            color: #005cc5
        }

        .cm-s-github-light .cm-variable {
            font-weight: normal;
            font-style: normal;
            text-decoration: none;
            color: #e36209
        }

        .file-editor-textarea {
            width: 100%;
            padding: 5px 4px;
            font: 12px "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
            resize: vertical;
            border: 0;
            border-radius: 0;
            outline: none
        }
    </style>

    <script>
        var responseHeaderCodeEditor, responseBodyCodeEditor, requestJsonCodeEditor;

        var loginApi = function(formDom) {
            $.ajax({
                url: $('#loginUrl').val(),
                type: 'POST',
                cache: false,
                data: new FormData(formDom),
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = jQuery.ajaxSettings.xhr();
                    var setRequestHeader = xhr.setRequestHeader;
                    xhr.setRequestHeader = function(name, value) {
                        var force = (typeof value['force'] === 'undefined') ? false : value['force'];
                        if (name === 'X-Requested-With' && !force) return;
                        setRequestHeader.call(this, name, value);
                    }
                    return xhr;
                },
                beforeSend: function(xhr) {
                    $(formDom).find('button[type="submit"]').attr('disabled', 'disabled');
                    $('#token').val('');
                },
                complete: function(xhr, status) {
                    $(formDom).find('button[type="submit"]').removeAttr('disabled');
                    var list = xhr.getAllResponseHeaders();
                    if (status === 'error') {
                        alert('Login Failed');
                        return false;
                    }
                    var data = JSON.parse(xhr.responseText);
                    $('#token').val({!! $loginToken !!});
                }
            });
        };

        var requestApi = function(formDom) {
            var headerKeyInputs = $('#headersContainer').find('input[id$="_key"]');

            for (var i = 0; i < headerKeyInputs.length; i++) {
                var uuid = headerKeyInputs[i].id.match(/^(header|param)(?:_)(.*)(?:_(key|value))$/);
                $('#headersContainer').find(`input[id$="${uuid[2]}_value"]`).attr('name', headerKeyInputs[i].value);
            }

            var paramKeyInputs = $('#paramsContainer').find('input[id$="_key"]');

            for (var i = 0; i < paramKeyInputs.length; i++) {
                var uuid = paramKeyInputs[i].id.match(/^(header|param)(?:_)(.*)(?:_(key|value))$/);
                $('#paramsContainer').find(`input[id$="${uuid[2]}_value"],textarea[id$="${uuid[2]}_value"]`).attr('name', paramKeyInputs[i].value);
            }

            $(formDom).find('button[type="submit"]').attr('disabled', 'disabled');
            responseHeaderCodeEditor.setValue('');
            responseBodyCodeEditor.setValue('');

            var requestData = (function() {
                if ($('#header_content_type_value').val() === 'application/octet-stream') {
                    return (function(hex) {
                        hex = hex.replace(/[^0-9A-Fa-f]/g, '');
                        hex = hex.length % 2 ? '0' + hex : hex;
                        return new Uint8Array(hex.match(/[\da-fA-F]{2}/gi).map(function (h) {
                            return parseInt(h, 16);
                        }));
                    })(requestJsonCodeEditor.getValue());
                }

                switch ($('#method').val()) {
                    case 'GET':
                    case 'DELETE':
                    case 'OPTIONS':
                        return $('#paramsContainer').serialize();
                    default:
                        switch ($('#header_content_type_value').val()) {
                            case '':
                                return new FormData($('#paramsContainer')[0]);
                            case 'application/x-www-form-urlencoded':
                                return $('#paramsContainer').serialize();
                            default:
                                return requestJsonCodeEditor.getValue();
                        }
                }
            })();

            var getData = (url, method, headers, data, getBlob) => {
                return new Promise((resolve, reject) => {
                    var sendWithData = true;
                    if (['GET', 'DELETE', 'OPTIONS'].indexOf($('#method').val()) > -1 && typeof data === 'string') {
                        url = data ? (url.match(/\?/) ? `${url}&${data}` : `${url}?${data}`) : url;
                        sendWithData = false;
                    }
                    const xhr = new XMLHttpRequest();

                    xhr.open(method.toUpperCase(), url, true);
                    if (typeof getBlob !== 'undefined') {
                        xhr.responseType = 'blob';
                    }
                    xhr.onload = () => {
                        if (xhr.status === 200) {
                            resolve(xhr, 'success');
                        } else {
                            reject(xhr, 'error');
                        }
                    };
                    xhr.onerror = () => {
                        reject(xhr, 'error');
                    };

                    var setRequestHeader = xhr.setRequestHeader;
                    xhr.setRequestHeader = function(name, value) {
                        var force = (typeof value['force'] === 'undefined') ? false : value['force'];
                        if (name === 'X-Requested-With' && !force) return;
                        setRequestHeader.call(this, name, value);
                    }

                    xhr.setRequestHeader('Authorization', 'Bearer ' + $('#token').val());
                    for (var i in headers) {
                        if (i && headers[i]) {
                            (function(k, v) {
                                xhr.setRequestHeader(k, {toString: () => { return v; }, force: true}, true);
                            })(i, headers[i]);
                        }
                    }

                    if (sendWithData) {
                        xhr.send(data);
                    } else {
                        xhr.send();
                    }
                });
            };

            var renderData = function(xhr, status) {
                if (xhr instanceof Error) {
                    throw xhr;
                }

                $(formDom).find('button[type="submit"]').removeAttr('disabled');
                var list = xhr.getAllResponseHeaders();
                if (list) {
                    responseHeaderCodeEditor.setValue(list);
                    var authorization = list.match(/^authorization: Bearer (.*)$/m);
                    if (authorization) {
                        $('#token').val(authorization[1]);
                    }
                }
                try {
                    if (xhr.responseText) {
                        responseBodyCodeEditor.setValue(xhr.responseText);
                    }
                } catch(e) {
                    var reader = new FileReader();
                    reader.onload = function(event){
                        responseBodyCodeEditor.setValue(reader.result);
                    };
                    reader.readAsText(xhr.response, 'utf-8');
                }
            };

            var saveFile = function(xhr) {
                $(formDom).find('button[type="submit"]').removeAttr('disabled');
                
                if (typeof xhr.getAllResponseHeaders !== 'undefined') {
                    responseHeaderCodeEditor.setValue(xhr.getAllResponseHeaders());
                }

                var fileName = (xhr.getResponseHeader('Content-Disposition') ?? '').match(/filename="?(.*?)"?$/);

                if (fileName && typeof fileName[1] !== 'undefined') {
                    if (window.navigator.msSaveOrOpenBlob) {
                        navigator.msSaveBlob(xhr.response, filename);
                    } else {
                        const link = document.createElement('a');
                        const body = document.querySelector('body');

                        link.href = window.URL.createObjectURL(xhr.response); // 创建对象url
                        link.download = fileName[1];

                        // fix Firefox
                        link.style.display = 'none';
                        body.appendChild(link);

                        link.click();
                        body.removeChild(link);

                        window.URL.revokeObjectURL(link.href); // 通过调用 URL.createObjectURL() 创建的 URL 对象
                    }
                    return;
                }
                var reader = new FileReader();
                reader.onload = function(event){
                    responseBodyCodeEditor.setValue(reader.result);
                };
                reader.readAsText(xhr.response, 'utf-8');
            };

            if ($('#isDownloadFile').prop('checked')) {
                getData($('#apiUrl').val(), $('#method').val(), $('#headersContainer').serializeFormToJson(), requestData, true)
                    .then(saveFile)
                    .catch(renderData);
            } else {
                getData($('#apiUrl').val(), $('#method').val(), $('#headersContainer').serializeFormToJson(), requestData)
                    .then(renderData)
                    .catch(renderData);
            }
        };

        $(function() {
            $.fn.serializeFormToJson = function() {
                var arr = $(this).serializeArray();
                var param = {};
                $.each(arr,function(i,obj) {
                    param[obj.name] = obj.value;
                });
                return param;
            };

            responseHeaderCodeEditor = CodeMirror.fromTextArea(document.getElementById('responseHeader'), {
                // mode: "shell",
                theme: "github-light",
                lineNumbers: true,
                scrollbarStyle: "simple"
                // lineWrapping: true
            });

            responseBodyCodeEditor = CodeMirror.fromTextArea(document.getElementById('responseBody'), {
                mode: "javascript",
                theme: "github-light",
                matchBrackets: true,
                lineNumbers: true,
                indentUnit: 4,
                scrollbarStyle: "simple",
                lineWrapping: true
            });

            requestJsonCodeEditor = CodeMirror.fromTextArea(document.getElementById('stringToPost'), {
                mode: "javascript",
                theme: "github-light",
                matchBrackets: true,
                lineNumbers: true,
                indentUnit: 4,
                scrollbarStyle: "simple",
                lineWrapping: true
            });

            requestJsonCodeEditor.setSize(null, '200px');
            $('#stringContainer').hide();

            $(window).resize(function(event) {
                var baseHeight = window.innerHeight;
                // var baseHeight = window.innerHeight < document.getElementById('leftPanel').offsetHeight ? (document.getElementById('leftPanel').offsetHeight + 76) : window.innerHeight;
                var size = (baseHeight - 150 < 0 ? 300 : (baseHeight - 150) / 3);
                responseHeaderCodeEditor.setSize(null, size + 'px');
                responseBodyCodeEditor.setSize(null, (size * 2) + 'px');
            });

            var baseHeight = window.innerHeight;
            // var baseHeight = window.innerHeight < (document.getElementById('leftPanel').offsetHeight + 98) ? (document.getElementById('leftPanel').offsetHeight + 174) : window.innerHeight;
            var size = (baseHeight - 150 < 0 ? 300 : (baseHeight - 150) / 3);
            responseHeaderCodeEditor.setSize(null, size + 'px');
            responseBodyCodeEditor.setSize(null, (size * 2) + 'px');

            var uuid = function() {
                var temp_url = URL.createObjectURL(new Blob());
                var uuid = temp_url.toString();
                URL.revokeObjectURL(temp_url);
                return uuid.substr(uuid.lastIndexOf("/") + 1);
            };

            var inputDomRender = function(name, type) {
                var domName = name + '_' + uuid(),
                    value = typeof arguments[2] === 'undefined' ? {'key': '', 'value': ''} : arguments[2],
                    description = typeof arguments[3] === 'undefined' ? null : arguments[3];

                var rowDom = $('<div>').addClass('row').css('margin-bottom', '15px'),
                    contianerDom = $('<div>').addClass('col-xs-12'),
                    inputGroupDom = $('<div>').addClass('input-group').css('height', '100%'),
                    keyInputDom = $('<input>').addClass('form-control')
                        .attr('id', `${domName}_key`)
                        .attr('type', 'text')
                        .attr('placeholder', 'Key')
                        .attr('value', value['key'])
                        .attr('aria-describedby', domName)
                        .css('height', '100%'),
                    inputGroupAddonDom = $('<span>').attr('id', domName)
                        .addClass('input-group-addon')
                        .attr('autocomplete', 'off')
                        .text(':'),
                    deleteButtonDom = $('<a>').attr('role', 'button')
                        .addClass('btn btn-default')
                        .attr('href', '####')
                        .html('<span class="glyphicon glyphicon-minus"></span>'),
                    deleteButtonContainerDom = $('<span>').addClass('input-group-addon').css('border', 0).css('padding', 0),
                    descriptionDom = $('<p>').addClass('help-block');

                switch (type) {
                    case 'file':
                        var valueInputDom = $('<input>').addClass('form-control')
                            .attr('id', `${domName}_value`)
                            .attr('type', 'file')
                            .attr('aria-describedby', domName);
                        break;
                    case 'textarea':
                        var valueInputDom = $('<textarea>').addClass('form-control')
                            .attr('id', `${domName}_value`)
                            .attr('type', 'file')
                            .attr('aria-describedby', domName)
                            .attr('rows', '5')
                            .val(value['value']);
                        break;
                    default:
                        var valueInputDom = $('<input>').addClass('form-control')
                            .attr('id', `${domName}_value`)
                            .attr('type', 'text')
                            .attr('placeholder', 'Value')
                            .attr('autocomplete', 'off')
                            .val(value['value'])
                            .attr('aria-describedby', domName);
                }

                var renderDom = rowDom.append(
                    contianerDom.append(
                        inputGroupDom.append(
                            keyInputDom
                        ).append(
                            inputGroupAddonDom
                        ).append(
                            valueInputDom
                        ).append(
                            deleteButtonContainerDom.append(
                                deleteButtonDom.click(function() {
                                    renderDom.remove();
                                })
                            )
                        )
                    ).append(
                        description ? descriptionDom.html(description.replace(/\n/g, '<br />')).css('margin-bottom', '-10px') : ''
                    )
                );

                return renderDom;
            };

            // $('#headersContainer').append(inputDomRender('header', 'text', {'key': 'Content-Type', 'value': 'application/x-www-form-urlencoded'}));
            $('#headersContainer').append(inputDomRender('header', 'text', {'key': 'X-Requested-With', 'value': 'XMLHttpRequest'}));
            $('#paramsContainer').append(inputDomRender('param', 'text'));

            $('#addHeaderButton').click(function() {
                $('#headersContainer').append(inputDomRender('header', 'text'));
            });

            $('#addParamButton').click(function() {
                $('#paramsContainer').append(inputDomRender('param', 'text'));
            });

            $('#addFileButton').click(function() {
                $('#paramsContainer').append(inputDomRender('param', 'file'));
            });

            $('#method').change(function() {
                switch ($(this).val()) {
                    case 'GET':
                    case 'DELETE':
                    case 'OPTIONS':
                        $('#header_content_type_value').removeAttr('name');
                        $('#header_content_type_row').hide();
                        break;
                    default:
                        $('#header_content_type_value').attr('name', 'Content-Type');
                        $('#header_content_type_row').show();
                        break;
                }
            }).select2({
                tags: true
            });

            $('#header_content_type_value').change(function() {
                switch ($(this).val()) {
                    case '':
                    case 'application/x-www-form-urlencoded':
                        requestJsonCodeEditor.setValue('');
                        $('#stringContainer').hide();
                        $('#paramsContainer').show();
                        $('#addParamButton').show();
                        $('#addFileButton').show();
                        break;
                        
                    default:
                        $('#stringContainer').show();
                        $('#paramsContainer').hide();
                        $('#addParamButton').hide();
                        $('#addFileButton').hide();
                        break;
                }
            }).select2({
                tags: true
            });

            var apiList = {};

            var changeApi = function(api) {
                var willClearCheckboxList = ['isDownloadFile'];
                for (var i in willClearCheckboxList) {
                    $(`#${willClearCheckboxList[i]}`).prop('checked', '');
                }

                $('#headersContainer').find(`a`).trigger('click');
                $('#header_content_type_value').val('').trigger('change');
                for (var i in api['headers']) {
                    if (i === 'Content-Type') {
                        $('#header_content_type_value').val(api['headers'][i]).trigger('change');
                        continue;
                    }
                    $('#headersContainer').append(inputDomRender('header', 'text', {'key': i, 'value': api['headers'][i]}));
                }
                $('#headersContainer').append(inputDomRender('header', 'text', {'key': 'X-Requested-With', 'value': 'XMLHttpRequest'}));
                $('#headersContainer').append(inputDomRender('header', 'text'));

                $('#method').val(api['method']).change();

                if (typeof api['headers'] === 'undefined' || typeof api['headers']['Content-Type'] === 'undefined') {
                    var apiContentType = '';
                } else {
                    var apiContentType = api['headers']['Content-Type'];
                }

                switch (apiContentType) {
                    case '':
                    case 'application/x-www-form-urlencoded':
                        requestJsonCodeEditor.setValue('');
                        $('#stringContainer').hide();
                        $('#paramsContainer').show();
                        $('#addParamButton').show();
                        $('#addFileButton').show();

                        $('#paramsContainer').find(`a`).trigger('click');
                        $('#paramsContainer').find(`a`).trigger('click');
                        // {'params': {'key': {'type': 'text', 'value' => 'value', 'description': 'description'}}}
                        // {'params': {'key': {'type': 'file', 'value' => 'value', 'description': 'description'}}}
                        // {'params': {'key': {'type': 'array', 'value' => ['value1', 'value2'], 'description': 'description'}}}
                        for (var i in api['params']) {
                            if (typeof api['params'][i] === 'object') {
                                switch (api['params'][i]['type']) {
                                    case 'file':
                                    case 'text':
                                    case 'textarea':
                                        $('#paramsContainer').append(
                                            inputDomRender(
                                                'param',
                                                api['params'][i]['type'],
                                                {
                                                    'key': i,
                                                    'value': (typeof api['params'][i]['value'] !== 'undefined' ? api['params'][i]['value'] : ''),
                                                },
                                                (typeof api['params'][i]['description'] !== 'undefined' ? api['params'][i]['description'] : null)
                                            )
                                        );
                                        break;
                                    case 'array':
                                        for (var k in api['params'][i]['value']) {
                                            $('#paramsContainer').append(
                                                inputDomRender(
                                                    'param',
                                                    'text',
                                                    {
                                                        'key': `${i}[]`,
                                                        'value': api['params'][i]['value'][k],
                                                    },
                                                    (typeof api['params'][i]['description'] !== 'undefined' ? api['params'][i]['description'] : null)
                                                )
                                            );
                                        }
                                        break;
                                }
                            } else {
                                $('#paramsContainer').append(inputDomRender('param', 'text', {'key': i, 'value': api['params'][i]}));
                            }
                        }
                        $('#paramsContainer').append(inputDomRender('param', 'text'));

                        break;
                    case 'application/octet-stream':
                        requestJsonCodeEditor.setValue(api['params']);
                        $('#stringContainer').show();
                        $('#paramsContainer').hide();
                        $('#addParamButton').hide();
                        $('#addFileButton').hide();
                        break;
                    default:
                        requestJsonCodeEditor.setValue(JSON.stringify(api['params'], null, "    "));
                        $('#stringContainer').show();
                        $('#paramsContainer').hide();
                        $('#addParamButton').hide();
                        $('#addFileButton').hide();
                        break;
                }

                $('#apiUrl').val(api['url']);

                if (typeof api['config'] !== 'undefined') {
                    for (var i in api['config']) {
                        if (api['config'][i]) {
                            $(`#${i}`).prop('checked', 'checked');
                        } else {
                            $(`#${i}`).removeProp('checked');
                        }
                    }
                }
            };

            var formatApi = function (api) {
                if (api.loading) {
                    return api.text;
                }

                return api.name;
            };

            var formatApiSelection = function (api) {
                return api.name || api.text;
            };

            $('#apiList').select2({
                ajax: {
                    url: function (params) {
                        return '{{ $apiListUrl }}';
                    },
                    data: function (params) {
                        return {
                            search: params.term
                        };
                    },
                    processResults: function (data, params) {
                        return {
                            results: data,
                        };
                    },
                    cache: false
                },
                placeholder: 'Search for a api',
                templateResult: formatApi,
                templateSelection: formatApiSelection
            });

            $('#apiList').on('select2:select', function (e) {
                var data = e.params.data;
                changeApi(data);
            });
        });
    </script>
</html>
