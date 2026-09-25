<?php
namespace App\Http\Controllers;

use App\Utilities\Translator;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.administration.language.list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $alert_col = "col-xl-4 offset-xl-4 col-lg-6 offset-lg-3";
        return view('backend.administration.language.create', compact('alert_col'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $this->validate($request, [
            'language_name' => 'required|alpha|string|max:30',
        ]);

        $name = $request->language_name;

        if (file_exists(resource_path() . "/language/$name.php")) {
            return redirect()->back()->with('error', _lang('Language already exists !'));
        }

        $language = file_get_contents(resource_path() . "/language/language.php");
        $new_file = fopen(resource_path() . "/language/$name.php", 'w+');
        fwrite($new_file, $language);
        fclose($new_file);

        return redirect()->route('languages.index')->with('success', _lang('Language Created successfully'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $name
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (file_exists(resource_path() . "/language/$id.php")) {
            require resource_path() . "/language/$id.php";

            //Find New Language key
            $language_2 = Translator::get_language_key();
            $new_keys   = array_diff_key($language_2, $language);

            $language = array_merge($language, $new_keys);

            $supportedLanguages = $this->getSupportedLanguages();
            $detectedLangCode   = $this->detectLanguageCode($id);

            return view('backend.administration.language.edit', compact('language', 'id', 'supportedLanguages', 'detectedLangCode'));
        }

        return redirect()->route('languages.index')->with('error', _lang('Language file not found!'));
    }

    public function auto_translate(Request $request)
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $request->validate([
            'texts'       => 'required|array',
            'target_lang' => 'required|string|max:15',
            'source_lang' => 'nullable|string|max:15',
        ]);

        $texts      = $request->texts;
        $targetLang = trim($request->target_lang);
        $sourceLang = trim($request->source_lang ?: 'auto');

        $translations = [];

        try {
            // Group texts by chunks of 25 for fast and reliable batch translation
            $chunks = array_chunk($texts, 25, true);

            foreach ($chunks as $chunk) {
                $joined = implode("\n", array_values($chunk));

                $response = \Illuminate\Support\Facades\Http::timeout(15)
                    ->asForm()
                    ->post('https://translate.googleapis.com/translate_a/single', [
                        'client' => 'gtx',
                        'sl'     => $sourceLang,
                        'tl'     => $targetLang,
                        'dt'     => 't',
                        'q'      => $joined,
                    ]);

                if ($response->successful()) {
                    $json           = $response->json();
                    $fullTranslated = '';
                    if (is_array($json) && isset($json[0]) && is_array($json[0])) {
                        foreach ($json[0] as $segment) {
                            if (isset($segment[0])) {
                                $fullTranslated .= $segment[0];
                            }
                        }
                    }

                    $translatedLines = explode("\n", $fullTranslated);
                    $keys            = array_keys($chunk);

                    if (count($translatedLines) === count($keys)) {
                        foreach ($keys as $idx => $originalKey) {
                            $translatedText = isset($translatedLines[$idx]) ? trim($translatedLines[$idx]) : '';
                            $translations[$originalKey] = $translatedText !== '' ? $translatedText : $this->translateSingle($chunk[$originalKey], $targetLang, $sourceLang);
                        }
                    } else {
                        // Fallback to translating individually if line counts mismatch
                        foreach ($chunk as $k => $text) {
                            $translations[$k] = $this->translateSingle($text, $targetLang, $sourceLang);
                        }
                    }
                } else {
                    foreach ($chunk as $k => $text) {
                        $translations[$k] = $this->translateSingle($text, $targetLang, $sourceLang);
                    }
                }
            }

            return response()->json([
                'result'       => 'success',
                'translations' => $translations,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'result'  => 'error',
                'message' => _lang('Translation error: ') . $e->getMessage(),
            ], 500);
        }
    }

    private function translateSingle(string $text, string $targetLang, string $sourceLang = 'auto'): string
    {
        if (trim($text) === '') {
            return '';
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(8)
                ->get('https://translate.googleapis.com/translate_a/single', [
                    'client' => 'gtx',
                    'sl'     => $sourceLang,
                    'tl'     => $targetLang,
                    'dt'     => 't',
                    'q'      => $text,
                ]);

            if ($response->successful()) {
                $json = $response->json();
                if (is_array($json) && isset($json[0]) && is_array($json[0])) {
                    $res = '';
                    foreach ($json[0] as $segment) {
                        if (isset($segment[0])) {
                            $res .= $segment[0];
                        }
                    }
                    return trim($res) ?: $text;
                }
            }
        } catch (\Throwable $e) {
        }

        return $text;
    }

    private function getSupportedLanguages(): array
    {
        return [
            'bn'    => 'Bengali (বাংলা)',
            'ar'    => 'Arabic (العربية)',
            'hi'    => 'Hindi (हिन्दी)',
            'es'    => 'Spanish (Español)',
            'fr'    => 'French (Français)',
            'de'    => 'German (Deutsch)',
            'ru'    => 'Russian (Русский)',
            'pt'    => 'Portuguese (Português)',
            'tr'    => 'Turkish (Türkçe)',
            'it'    => 'Italian (Italiano)',
            'ur'    => 'Urdu (اردو)',
            'id'    => 'Indonesian (Bahasa Indonesia)',
            'ms'    => 'Malay (Bahasa Melayu)',
            'zh-CN' => 'Chinese Simplified (简体中文)',
            'zh-TW' => 'Chinese Traditional (繁體中文)',
            'ja'    => 'Japanese (日本語)',
            'ko'    => 'Korean (한국어)',
            'th'    => 'Thai (ไทย)',
            'vi'    => 'Vietnamese (Tiếng Việt)',
            'fa'    => 'Persian (فارسی)',
            'nl'    => 'Dutch (Nederlands)',
            'pl'    => 'Polish (Polski)',
            'sv'    => 'Swedish (Svenska)',
            'el'    => 'Greek (Ελληνικά)',
            'he'    => 'Hebrew (עברית)',
            'ro'    => 'Romanian (Română)',
            'hu'    => 'Hungarian (Magyar)',
            'cs'    => 'Czech (Čeština)',
            'da'    => 'Danish (Dansk)',
            'fi'    => 'Finnish (Suomi)',
            'no'    => 'Norwegian (Norsk)',
            'uk'    => 'Ukrainian (Українська)',
            'ta'    => 'Tamil (தமிழ்)',
            'te'    => 'Telugu (తెలుగు)',
            'mr'    => 'Marathi (मराठी)',
            'gu'    => 'Gujarati (ગુજરાતી)',
            'kn'    => 'Kannada (ಕನ್ನಡ)',
            'ml'    => 'Malayalam (മലയാളം)',
            'pa'    => 'Punjabi (ਪੰਜਾਬੀ)',
            'en'    => 'English',
        ];
    }

    private function detectLanguageCode(string $name): string
    {
        $normalized = strtolower(trim($name));
        $map = [
            'bengali'   => 'bn',
            'bangla'    => 'bn',
            'arabic'    => 'ar',
            'hindi'     => 'hi',
            'spanish'   => 'es',
            'french'    => 'fr',
            'german'    => 'de',
            'russian'   => 'ru',
            'portuguese'=> 'pt',
            'turkish'   => 'tr',
            'italian'   => 'it',
            'urdu'      => 'ur',
            'indonesian'=> 'id',
            'malay'     => 'ms',
            'chinese'   => 'zh-CN',
            'japanese'  => 'ja',
            'korean'    => 'ko',
            'thai'      => 'th',
            'vietnamese'=> 'vi',
            'persian'   => 'fa',
            'farsi'     => 'fa',
            'dutch'     => 'nl',
            'polish'    => 'pl',
            'swedish'   => 'sv',
            'greek'     => 'el',
            'hebrew'    => 'he',
            'romanian'  => 'ro',
            'hungarian' => 'hu',
            'czech'     => 'cs',
            'danish'    => 'da',
            'finnish'   => 'fi',
            'norwegian' => 'no',
            'ukrainian' => 'uk',
            'tamil'     => 'ta',
            'telugu'    => 'te',
            'marathi'   => 'mr',
            'gujarati'  => 'gu',
            'kannada'   => 'kn',
            'malayalam' => 'ml',
            'punjabi'   => 'pa',
            'english'   => 'en',
        ];

        foreach ($map as $key => $code) {
            if (str_contains($normalized, $key)) {
                return $code;
            }
        }

        if (array_key_exists($normalized, $this->getSupportedLanguages())) {
            return $normalized;
        }

        return 'bn';
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        require resource_path() . "/language/$id.php";

        //Find New Language key
        $newLanguage = [];
        foreach ($_POST['language'] as $key => $value) {
            $newLanguage[str_replace("_", " ", $key)] = $value;
        }

        $new_keys = array_diff_key($newLanguage, $language);

        $language = array_merge($language, $new_keys);

        foreach ($_POST['language'] as $key => $value) {
            $language[str_replace("_", " ", $key)] = $value;
        }

        $contents = "<?php \n\n";
        $contents .= '$language=array();' . "\n\n";
        foreach ($language as $key => $value) {
            $l_value = str_replace('"', '', $value);
            $contents .= '$language["' . str_replace("_", " ", $key) . '"]="' . $l_value . '";' . "\n";
        }

        $file = fopen(resource_path() . "/language/$id.php", "w");

        if (fwrite($file, $contents)) {
            if ($request->ajax()) {
                return response()->json(['result' => 'success', 'message' => _lang('Updated successfully')]);
            } else {
                return redirect()->route('languages.index')->with('success', _lang('Updated successfully'));
            }
        } else {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => _lang('Update failed !')]);
            } else {
                return redirect()->route('languages.index')->with('success', _lang('Update failed !'));
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (file_exists(resource_path() . "/language/$id.php")) {
            unlink(resource_path() . "/language/$id.php");
            return redirect()->route('languages.index')->with('success', _lang('Removed successfully'));
        }
    }
}
