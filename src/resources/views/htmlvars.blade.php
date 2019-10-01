@push('htmlvars')

<input type="hidden" id="ftp" value="{{ !!$ftp }}" />
<input type="hidden" id="popup" value="{{ $popup }}" />
<input type="hidden" id="callback" value="{{ $callback; }}>" />
<input type="hidden" id="crossdomain" value="{{ $crossdomain }}" />
<input type="hidden" id="editor" value="{{ $editor }}" />
<input type="hidden" id="view" value="{{ $view }}" />
<input type="hidden" id="subdir" value="{{ $subdir }}" />
<input type="hidden" id="field_id" value="{{ $field_id }}" />
<input type="hidden" id="multiple" value="{{ $multiple }}" />
<input type="hidden" id="type_param" value="{{ $type_param }}" />
<input type="hidden" id="upload_dir" value="{{ $upload_dir }}" />
<input type="hidden" id="cur_dir" value="{{ $cur_dir }}" />
<input type="hidden" id="cur_dir_thumb" value="{{ $cur_dir_thumb }}" />
<input type="hidden" id="duplicate" value="{{ $duplicate }}" />
<input type="hidden" id="base_url" value="{{ $base_url }}"/>
<input type="hidden" id="fldr_value" value="{{ $fldr_value }}"/>
<input type="hidden" id="sub_folder" value="{{ $sub_folder }}"/>
<input type="hidden" id="return_relative_url" value="{{ $return_relative_url }}"/>
<input type="hidden" id="file_number_limit_js" value="{{ $file_number_limit_js }}" />
<input type="hidden" id="sort_by" value="{{ $sort_by }}" />
<input type="hidden" id="descending" value="{{ $descending }}" />
<input type="hidden" id="current_url" value="{{ $current_url }}" />
<input type="hidden" id="copy_cut_files_allowed" value="{{ $copy_cut_files_allowed }}" />
<input type="hidden" id="copy_cut_dirs_allowed" value="{{ $copy_cut_dirs_allowed }}" />
<input type="hidden" id="copy_cut_max_size" value="{{ $copy_cut_max_size }}" />
<input type="hidden" id="copy_cut_max_count" value="{{ $copy_cut_max_count }}" />
<input type="hidden" id="insert_folder_name" value="{{ __('Insert_Folder_Name') }}" />
<input type="hidden" id="rename_existing_folder" value="{{ __('Rename_existing_folder') }}" />
<input type="hidden" id="new_folder" value="{{ __('New_Folder') }}" />
<input type="hidden" id="ok" value="{{ __('OK') }}" />
<input type="hidden" id="cancel" value="{{ __('Cancel') }}" />
<input type="hidden" id="rename" value="{{ __('Rename') }}" />
<input type="hidden" id="lang_duplicate" value="{{ __('Duplicate') }}" />
<input type="hidden" id="lang_show_url" value="{{ __('Show_url') }}" />
<input type="hidden" id="lang_copy" value="{{ __('Copy') }}" />
<input type="hidden" id="lang_cut" value="{{ __('Cut') }}" />
<input type="hidden" id="lang_paste" value="{{ __('Paste') }}" />
<input type="hidden" id="lang_paste_here" value="{{ __('Paste_Here') }}" />
<input type="hidden" id="lang_paste_confirm" value="{{ __('Paste_Confirm') }}" />
<input type="hidden" id="lang_files" value="{{ __('Files') }}" />
<input type="hidden" id="lang_folders" value="{{ __('Folders') }}" />
<input type="hidden" id="lang_files_on_clipboard" value="{{ __('Files_ON_Clipboard') }}" />
<input type="hidden" id="lang_clear_clipboard_confirm" value="{{ __('Clear_Clipboard_Confirm') }}" />
<input type="hidden" id="lang_file_permission" value="{{ __('File_Permission') }}" />
<input type="hidden" id="lang_edit_image" value="{{  }} __('Edit_image') }}" />
<input type="hidden" id="lang_error_upload" value="{{ __('Edit_File') }}>
<input type="hidden" id="lang_new_file" value="{{ __('New_File') }}" />
<input type="hidden" id="lang_filename" value="{{ __('Filename') }}" />
<input type="hidden" id="lang_edit_file" value="{{ __('Edit_File') }}" />
<input type="hidden" id="lang_new_file" value="{{ __('New_File') }}" />
<input type="hidden" id="lang_filename" value="{{ __('Filename') }}" />
<input type="hidden" id="lang_edit_image" value="{{ __('Edit_image') }}" />
<input type="hidden" id="lang_error_upload" value="{{ __('Error_Upload') }}" />
<input type="hidden" id="lang_select" value="{{ __('Select') }}" />
<input type="hidden" id="lang_extract" value="{{ __('Extract') }}" />
<input type="hidden" id="lang_lang_change" value="{{ __('Lang_Change') }}" />
<input type="hidden" id="lang_file_info" value="{{ RFM::fixStrtoupper(__('File_info')) }}" />

<input type="hidden" id="clipboard" value="{{ $clipboard }}" />
<input type="hidden" id="chmod_files_allowed" value="{{ $chmod_files_allowed }}" />
<input type="hidden" id="chmod_dirs_allowed" value="{{ $chmod_dirs_allowed }}" />
<input type="hidden" id="edit_text_files_allowed" value="{{ $edit_text_files_allowed }}" />
<input type="hidden" id="extract_files_allowed" value="{{ $extract_files_allowed }}" />
<input type="hidden" id="replace_with" value="{{ $convert_spaces ? $replace_with : '' }}" />
<input type="hidden" id="transliteration" value="{{ $transliteration }}" />
<input type="hidden" id="convert_spaces" value="{{ $convert_spaces }}" />
<input type="hidden" id="lower_case" value="{{ $lower_case }}" />
<input type="hidden" id="show_folder_size" value="{{ $show_folder_size }}" />
<input type="hidden" id="add_time_to_img" value="{{ $add_time_to_img }}" />

@endpush