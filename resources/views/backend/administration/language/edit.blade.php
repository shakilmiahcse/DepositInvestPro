@extends('layouts.app')

@section('content')
<style>
.is-translated {
    border-color: #28a745 !important;
    background-color: #f6fff8 !important;
    transition: all 0.3s ease;
}
.phrase-col {
    transition: all 0.2s ease;
}
.translator-toolbar {
    background: #fdfdfd;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 24px;
    border: 1px solid #e3e6ef;
}
.badge-count {
    font-size: 0.85rem;
    font-weight: 600;
    padding: 5px 10px;
    border-radius: 6px;
}
.btn-translate-single {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    cursor: pointer;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center">
                <span class="header-title">{{ _lang('Update Translations') }} - <strong>{{ $id }}</strong></span>
                <div class="ml-auto d-flex align-items-center">
                    <a href="{{ route('languages.index') }}" class="btn btn-outline-secondary btn-xs mr-2">
                        <i class="ti-arrow-left"></i>&nbsp;{{ _lang('Back to Languages') }}
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- Auto Translator Toolbar -->
                <div class="translator-toolbar">
                    <div class="row align-items-center">
                        <div class="col-lg-3 col-md-4 mb-2 mb-lg-0">
                            <label class="control-label mb-1 font-weight-bold"><i class="ti-world mr-1 text-primary"></i>{{ _lang('Target Language') }}</label>
                            <select id="target_lang" class="form-control form-control-sm select2">
                                @foreach($supportedLanguages as $code => $name)
                                    <option value="{{ $code }}" {{ $detectedLangCode == $code ? 'selected' : '' }}>
                                        {{ $name }} ({{ $code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-5 col-md-8 mb-2 mb-lg-0 d-flex flex-wrap align-items-end">
                            <button type="button" class="btn btn-primary btn-sm mr-2 mb-1" id="btn-auto-translate-all">
                                <i class="ti-wand mr-1"></i>{{ _lang('Auto Translate All') }}
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm mr-2 mb-1" id="btn-translate-untranslated">
                                <i class="ti-bolt mr-1"></i>{{ _lang('Translate Untranslated') }}
                            </button>
                        </div>

                        <div class="col-lg-4 col-md-12">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="ti-search"></i></span>
                                </div>
                                <input type="text" id="phrase_search" class="form-control" placeholder="{{ _lang('Search phrases or translations...') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="clear-search"><i class="ti-close"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2 pt-2 border-top align-items-center">
                        <div class="col-12 d-flex flex-wrap align-items-center">
                            <span class="mr-3 text-muted small"><i class="ti-info-alt mr-1"></i>{{ _lang('Google Translator integration powered by Google Translate API.') }}</span>
                            <div class="ml-auto d-flex flex-wrap">
                                <span class="badge badge-light badge-count mr-2 border">
                                    {{ _lang('Total') }}: <strong id="badge-total">{{ count($language) }}</strong>
                                </span>
                                <span class="badge badge-success badge-count mr-2">
                                    {{ _lang('Translated') }}: <strong id="badge-translated">0</strong>
                                </span>
                                <span class="badge badge-warning badge-count">
                                    {{ _lang('Untranslated/Default') }}: <strong id="badge-untranslated">0</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="post" id="language-form" class="validate" autocomplete="off" action="{{ route('languages.update', $id) }}">
                    @csrf
                    <input name="_method" type="hidden" value="PATCH">

                    <div class="row" id="phrases-container">
                        @foreach($language as $key => $lang)
                        <div class="col-md-6 phrase-col">
                            <div class="form-group mb-3 phrase-group">
                                <label class="control-label font-weight-bold mb-1">{{ ucwords($key) }}</label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control language-field" 
                                           name="language[{{ str_replace(' ', '_', $key) }}]" 
                                           data-original="{{ $key }}" 
                                           value="{{ $lang }}" 
                                           required>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary btn-translate-single" title="{{ _lang('Translate this phrase') }}">
                                            <i class="ti-world"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <div class="col-md-12 mt-4 pt-3 border-top">
                            <div class="form-group d-flex align-items-center">
                                <button type="submit" class="btn btn-primary btn-lg submit-btn">
                                    <i class="ti-check-box mr-1"></i>{{ _lang('Save Translation') }}
                                </button>
                                <a href="{{ route('languages.index') }}" class="btn btn-outline-secondary btn-lg ml-3">
                                    {{ _lang('Cancel') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Translation Progress Modal -->
<div class="modal fade" id="translation-progress-modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold"><i class="ti-world text-primary mr-2"></i>{{ _lang('Auto Translating Phrases...') }}</h5>
            </div>
            <div class="modal-body py-4">
                <div class="text-center mb-3">
                    <h6 id="progress-status-text" class="text-muted">{{ _lang('Preparing phrases...') }}</h6>
                    <h3 id="progress-percentage" class="font-weight-bold text-primary mt-2">0%</h3>
                </div>
                <div class="progress" style="height: 18px; border-radius: 9px;">
                    <div id="translation-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="d-flex justify-content-between mt-2 text-muted small">
                    <span id="progress-count-text">0 / 0 {{ _lang('phrases') }}</span>
                    <span id="progress-batch-text">{{ _lang('Processing...') }}</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" id="btn-cancel-translation">
                    <i class="ti-close mr-1"></i>{{ _lang('Cancel Translation') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-script')
<script>
(function ($) {
    "use strict";

    var isTranslating = false;
    var cancelRequested = false;

    // Initialize counter badges
    function updateCounterBadges() {
        var total = $('.language-field').length;
        var untranslated = 0;
        $('.language-field').each(function () {
            var orig = $(this).data('original').toString().toLowerCase().trim();
            var val = $(this).val().toString().toLowerCase().trim();
            if (val === '' || val === orig) {
                untranslated++;
            }
        });
        var translated = total - untranslated;
        $('#badge-total').text(total);
        $('#badge-translated').text(translated);
        $('#badge-untranslated').text(untranslated);
    }

    updateCounterBadges();

    // Instant Filter / Search
    $('#phrase_search').on('input keyup', function () {
        var search = $(this).val().toLowerCase().trim();
        if (search === '') {
            $('.phrase-col').show();
            return;
        }
        $('.phrase-col').each(function () {
            var key = $(this).find('.language-field').data('original').toString().toLowerCase();
            var val = $(this).find('.language-field').val().toString().toLowerCase();
            if (key.indexOf(search) > -1 || val.indexOf(search) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $('#clear-search').on('click', function () {
        $('#phrase_search').val('').trigger('keyup');
    });

    // Progress Bar Updater
    function updateProgress(completed, total, batchInfo) {
        var percent = total > 0 ? Math.round((completed / total) * 100) : 0;
        $('#translation-progress-bar').css('width', percent + '%').attr('aria-valuenow', percent);
        $('#progress-percentage').text(percent + '%');
        $('#progress-count-text').text(completed + ' / ' + total + ' {{ _lang('phrases') }}');
        if (batchInfo) {
            $('#progress-batch-text').text(batchInfo);
        }
    }

    // Cancel Translation
    $('#btn-cancel-translation').on('click', function () {
        cancelRequested = true;
        $('#progress-status-text').text('{{ _lang('Cancelling...') }}');
    });

    // Auto Translate Runner
    async function runAutoTranslation(onlyUntranslated) {
        var targetLang = $('#target_lang').val();
        if (!targetLang) {
            Swal.fire({
                icon: 'warning',
                text: '{{ _lang('Please select a target language first') }}'
            });
            return;
        }

        var targetLangName = $('#target_lang option:selected').text();

        var confirmText = onlyUntranslated 
            ? '{{ _lang('Translate only untranslated phrases to') }} ' + targetLangName + '?'
            : '{{ _lang('Translate ALL phrases to') }} ' + targetLangName + '? {{ _lang('Existing translations will be updated.') }}';

        var confirmResult = await Swal.fire({
            title: '{{ _lang('Auto Translate with Google') }}',
            text: confirmText,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '{{ _lang('Yes, Start Translation') }}',
            cancelButtonText: '{{ _lang('Cancel') }}'
        });

        if (!confirmResult.value) {
            return;
        }

        var fieldsToTranslate = [];
        $('.language-field').each(function () {
            var original = $(this).data('original');
            var currentVal = $(this).val().trim();
            if (onlyUntranslated) {
                if (currentVal === '' || currentVal.toLowerCase() === original.toString().toLowerCase()) {
                    fieldsToTranslate.push({
                        el: $(this),
                        original: original
                    });
                }
            } else {
                fieldsToTranslate.push({
                    el: $(this),
                    original: original
                });
            }
        });

        if (fieldsToTranslate.length === 0) {
            Swal.fire({
                icon: 'info',
                text: '{{ _lang('No phrases need translation!') }}'
            });
            return;
        }

        var total = fieldsToTranslate.length;
        var completed = 0;
        isTranslating = true;
        cancelRequested = false;

        $('#progress-status-text').text('{{ _lang('Translating phrases...') }}');
        updateProgress(0, total, '{{ _lang('Starting...') }}');
        $('#translation-progress-modal').modal('show');

        var chunkSize = 25;
        var totalBatches = Math.ceil(total / chunkSize);

        for (var i = 0; i < total; i += chunkSize) {
            if (cancelRequested) {
                break;
            }

            var batchIndex = Math.floor(i / chunkSize) + 1;
            var batch = fieldsToTranslate.slice(i, i + chunkSize);
            var textsPayload = {};

            batch.forEach(function (item, index) {
                textsPayload[i + index] = item.original;
            });

            updateProgress(completed, total, '{{ _lang('Batch') }} ' + batchIndex + ' / ' + totalBatches);

            try {
                var response = await $.ajax({
                    method: 'POST',
                    url: '{{ route('languages.auto_translate') }}',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        target_lang: targetLang,
                        texts: textsPayload
                    }
                });

                if (response.result === 'success' && response.translations) {
                    batch.forEach(function (item, index) {
                        var translated = response.translations[i + index];
                        if (translated) {
                            item.el.val(translated).addClass('is-translated');
                        }
                        completed++;
                    });
                    updateProgress(completed, total, '{{ _lang('Batch') }} ' + batchIndex + ' / ' + totalBatches);
                    updateCounterBadges();
                } else {
                    completed += batch.length;
                    updateProgress(completed, total);
                }
            } catch (err) {
                console.error('Translation error on batch:', err);
                completed += batch.length;
                updateProgress(completed, total);
            }
        }

        isTranslating = false;
        $('#translation-progress-modal').modal('hide');

        Swal.fire({
            icon: 'success',
            title: '{{ _lang('Translation Finished!') }}',
            text: '{{ _lang('Successfully translated') }} ' + completed + ' {{ _lang('phrases.') }} {{ _lang('You can make changes or click Save Translation.') }}',
            confirmButtonText: '{{ _lang('OK') }}'
        });
    }

    // Trigger Auto Translate All
    $('#btn-auto-translate-all').on('click', function (e) {
        e.preventDefault();
        runAutoTranslation(false);
    });

    // Trigger Translate Untranslated Only
    $('#btn-translate-untranslated').on('click', function (e) {
        e.preventDefault();
        runAutoTranslation(true);
    });

    // Single Phrase Translate
    $(document).on('click', '.btn-translate-single', async function (e) {
        e.preventDefault();
        var btn = $(this);
        var input = btn.closest('.input-group').find('.language-field');
        var originalText = input.data('original');
        var targetLang = $('#target_lang').val();

        if (!originalText || !targetLang) return;

        btn.prop('disabled', true).find('i').removeClass('ti-world').addClass('fa fa-spinner fa-spin');

        try {
            var response = await $.ajax({
                method: 'POST',
                url: '{{ route('languages.auto_translate') }}',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    target_lang: targetLang,
                    texts: { 0: originalText }
                }
            });

            if (response.result === 'success' && response.translations && response.translations[0]) {
                input.val(response.translations[0]).addClass('is-translated');
                updateCounterBadges();
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                text: '{{ _lang('Failed to translate phrase') }}'
            });
        } finally {
            btn.prop('disabled', false).find('i').removeClass('fa fa-spinner fa-spin').addClass('ti-world');
        }
    });

    // Update Counter when user manually types
    $(document).on('input change', '.language-field', function () {
        updateCounterBadges();
    });

    // Save Translation form submission (chunked to prevent max_input_vars issues)
    $(document).on('submit', '#language-form', function (e) {
        e.preventDefault();

        var actionUrl = $(this).attr('action');
        var form = $(this);
        var submitBtn = $('.submit-btn');

        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>{{ _lang('Saving...') }}');

        $.ajax({
            method: "POST",
            url: actionUrl,
            data: $.param($(form).serializeArray().slice(0, 990)),
            success: function (data) {
                var secondBatchData = $(form).serializeArray().slice(990);
                if (secondBatchData.length > 0) {
                    secondBatchData.push({name: '_method', value: 'PATCH'});
                    secondBatchData.push({name: '_token', value: $('meta[name="csrf-token"]').attr('content')});

                    setTimeout(function () {
                        $.ajax({
                            method: "POST",
                            url: actionUrl,
                            data: $.param(secondBatchData),
                            success: function (data) {
                                var json = JSON.parse(JSON.stringify(data));
                                Swal.fire({
                                    text: json['message'],
                                    icon: json['result'],
                                    confirmButtonText: "{{ _lang('Close') }}",
                                });
                                submitBtn.prop('disabled', false).html('<i class="ti-check-box mr-1"></i>{{ _lang('Save Translation') }}');
                            },
                            error: function () {
                                submitBtn.prop('disabled', false).html('<i class="ti-check-box mr-1"></i>{{ _lang('Save Translation') }}');
                            }
                        });
                    }, 500);
                } else {
                    var json = JSON.parse(JSON.stringify(data));
                    Swal.fire({
                        text: json['message'],
                        icon: json['result'],
                        confirmButtonText: "{{ _lang('Close') }}",
                    });
                    submitBtn.prop('disabled', false).html('<i class="ti-check-box mr-1"></i>{{ _lang('Save Translation') }}');
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    text: '{{ _lang('Failed to save translation') }}'
                });
                submitBtn.prop('disabled', false).html('<i class="ti-check-box mr-1"></i>{{ _lang('Save Translation') }}');
            }
        });
    });

})(jQuery);
</script>
@endsection