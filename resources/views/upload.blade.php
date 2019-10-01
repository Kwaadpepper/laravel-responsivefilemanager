<!-- uploader div start -->
<div class="uploader">
    <div class="flex">
        <div class="text-center">
            <button class="btn btn-inverse close-uploader"><i class="icon-backward icon-white"></i>{{ __('Return_Files_List')}}</button>
        </div>
        <div class="space10"></div>
        <div class="tabbable upload-tabbable">
            <!-- Only required for left/right tabs -->
            <div class="container1">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#baseUpload" data-toggle="tab">{{ __('Upload_base') }}</a></li>
                    @if (config('rfm.url_upload'))
                    <li><a href="#urlUpload" data-toggle="tab">{{ __('Upload_url') }}</a></li>
                    @endif
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="baseUpload">
                        <!-- The file upload form used as target for the file upload widget -->
                        <form id="fileupload" action="{{ route('RFMUpload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="container2">
                                <div class="fileupload-buttonbar">
                                    <!-- The global progress state -->
                                    <div class="fileupload-progress">
                                        <!-- The global progress bar -->
                                        <div class="progress progress-striped active" role="progressbar"
                                            aria-valuemin="0" aria-valuemax="100">
                                            <div class="bar bar-success" style="width:0%;"></div>
                                        </div>
                                        <!-- The extended global progress state -->
                                        <div class="progress-extended"></div>
                                    </div>
                                    <div class="text-center">
                                        <!-- The fileinput-button span is used to style the file input field as button -->
                                        <span class="btn btn-success fileinput-button">
                                            <i class="glyphicon glyphicon-plus"></i>
                                            <span>{{ __('Upload_add_files') }}</span>
                                            <input type="file" name="files[]" multiple="multiple">
                                        </span>
                                        <button type="submit" class="btn btn-primary start">
                                            <i class="glyphicon glyphicon-upload"></i>
                                            <span>{{ __('Upload_start') }}</span>
                                        </button>
                                        <!-- The global file processing state -->
                                        <span class="fileupload-process"></span>
                                    </div>
                                </div>
                                <!-- The table listing the files available for upload/download -->
                                <div id="filesTable">
                                    <table role="presentation" class="table table-striped table-condensed small">
                                        <tbody class="files"></tbody>
                                    </table>
                                </div>
                                <div class="upload-help">{{ __('Upload_base_help') }}</div>
                            </div>
                        </form>
                        <!-- The template to display files available for upload -->
                        <script id="template-upload" type="text/x-tmpl">
                        {% for (var i=0, file; file=o.files[i]; i++) { %}
                            <tr class="template-upload">
                                <td>
                                    <span class="preview"></span>
                                </td>
                                <td>
                                    <p class="name">{%=file.relativePath%}{%=file.name%}</p>
                                    <strong class="error text-danger"></strong>
                                </td>
                                <td>
                                    <p class="size">Processing...</p>
                                    <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar bar-success" style="width:0%;"></div></div>
                                </td>
                                <td>
                                    {% if (!i && !o.options.autoUpload) { %}
                                        <button class="btn btn-primary start" disabled style="display:none">
                                            <i class="glyphicon glyphicon-upload"></i>
                                            <span>Start</span>
                                        </button>
                                    {% } %}
                                    {% if (!i) { %}
                                        <button class="btn btn-link cancel">
                                            <i class="icon-remove"></i>
                                        </button>
                                    {% } %}
                                </td>
                            </tr>
                        {% } %}
                    </script>
                        <!-- The template to display files available for download -->
                        <script id="template-download" type="text/x-tmpl">
                        {% for (var i=0, file; file=o.files[i]; i++) { %}
                            <tr class="template-download">
                                <td>
                                    <span class="preview">
                                        {% if (file.error) { %}
                                        <i class="icon icon-remove"></i>
                                        {% } else { %}
                                        <i class="icon icon-ok"></i>
                                        {% } %}
                                    </span>
                                </td>
                                <td>
                                    <p class="name">
                                        {% if (file.url) { %}
                                            <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                                        {% } else { %}
                                            <span>{%=file.name%}</span>
                                        {% } %}
                                    </p>
                                    {% if (file.error) { %}
                                        <div><span class="label label-danger">Error</span> {%=file.error%}</div>
                                    {% } %}
                                </td>
                                <td>
                                    <span class="size">{%=o.formatFileSize(file.size)%}</span>
                                </td>
                                <td></td>
                            </tr>
                        {% } %}
                    </script>
                    </div>
                    @if (config('rfm.url_upload'))
                    <div class="tab-pane" id="urlUpload">
                        <br />
                        <form class="form-horizontal">
                            <div class="control-group">
                                <label class="control-label" for="url">{{ __('Upload_url') }}</label>
                                <div class="controls">
                                    <input type="text" class="input-block-level" id="url"
                                        placeholder="{{ __('Upload_url') }}">
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="controls">
                                    <button class="btn btn-primary" id="uploadURL">{{ __('Upload_file') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>