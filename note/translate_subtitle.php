<?php
/**
 * Subtitle Translation Script
 * Translates English subtitles to Myanmar (Burmese)
 */

$inputFile = 'Mamasan.2025-tt38608640-WD.txt';
$outputFile = 'Mamasan.2025-tt38608640-WD-MM.txt';

// Translation dictionary for common phrases
$translations = [
    // Basic phrases
    'Sir, take it easy.' => 'ဆရာ၊ အေးအေးသက်သက် နေပါအောင်။',
    'What\'s going on here?' => 'ဒီမှာ ဘာဖြစ်နေတာလဲ?',
    'Are you okay?' => 'သင်နေကောင်းရဲ့လား?',
    'Yes, Mama.' => 'ဟုတ်ကဲ့၊ မာမာ။',
    
    // The specific phrases you asked about
    'Even I got my nipple bitten,' => 'ကျွန်တော်တောင် ရင်သပ်ကို ကိုက်ခံရပြီး၊',
    'and I just dealt with it.' => 'သာမန်အတိုင်း ဖြေရှင်းလိုက်တယ်။',
    'Well, at least I demanded' => 'ကောင်းပြီ၊ အနည်းဆုံးတော့ ကျွန်တော်က',
    'a huge tip from him.' => 'သူ့ဆီက ငွေကြေးအများကြီး တောင်းခံခဲ့တယ်။',
    'How else are we going to' => 'တစ်ခြားဘယ်လိုနည်းနဲ့ ကျွန်တော်တို့',
    'make the big money, huh?' => 'ငွေကြေးအများကြီး ရှာမှာလဲ၊ ဟင်။',
];

// Function to translate line
function translateLine($englishLine) {
    // Remove leading/trailing whitespace
    $englishLine = trim($englishLine);
    
    // Skip empty lines and timestamps
    if (empty($englishLine) || preg_match('/^\d{2}:\d{2}:\d{2},\d{3} -->/', $englishLine)) {
        return $englishLine;
    }
    
    // Skip subtitle numbers
    if (preg_match('/^\d+$/', $englishLine)) {
        return $englishLine;
    }
    
    // Skip header lines
    if (strpos($englishLine, '--==') !== false || strpos($englishLine, 'https://') !== false) {
        return $englishLine;
    }
    
    // Remove dialogue markers (-)
    $cleaned = preg_replace('/^-\s*/', '', $englishLine);
    
    // Simple translations - you can expand this
    $translation = translateToMyanmar($cleaned);
    
    return $translation;
}

// Translation function
function translateToMyanmar($english) {
    // Handle specific phrases first
    $specificPhrases = [
        "You're being so dramatic!" => "သင်အရမ်းအလွန်အကျွံလုပ်နေတာပဲ!",
        "I'm gonna pay you, right?" => "ငါပေးမှာပဲ၊ မဟုတ်လား?",
        "You're groping my breast!" => "သင်က ငါ့ရင်သားကို လိုက်ကိုင်နေတာပဲ!",
        "What if I did that to you?" => "ငါက သင့်ကို အဲဒါလုပ်ခဲ့ရင် ဘယ်လိုဖြစ်မလဲ?",
        "You need to talk to your people!" => "သင်က သင့်လူတွေကို ပြောဖို့လိုတယ်!",
        "Sir." => "ဆရာ။",
        "That's not allowed here." => "ဒါကို ဒီမှာ ခွင့်မပြုဘူး။",
        "Not allowed? What do you mean not allowed?" => "ခွင့်မပြုဘူးလား? ခွင့်မပြုဘူးလို့ ဘာကိုဆိုလိုတာလဲ?",
        "I don't want him anymore!" => "ငါက သူ့ကို မလိုချင်တော့ဘူး!",
        "His breath stinks!" => "သူ့အသက်နံ့ အနံ့ဆိုးတယ်!",
        "This bitch!" => "ဒီကောင်!",
        "Sir, just take it easy!" => "ဆရာ၊ အေးအေးသက်သက် နေပါအောင်!",
        "Hold on a minute." => "ခဏစောင့်ပါအောင်။",
        "Sir, stay right here." => "ဆရာ၊ ဒီမှာပဲ ရပ်နေပါ။",
        "And you! You've been at this for a while!" => "ပြီးတော့ သင်က! သင်က အဲဒါကို ကြာပြီနေပြီ!",
        "You're taking it too far!" => "သင်က အရမ်းလွန်သွားပြီ!",
        "Sir, you're totally wasted." => "ဆရာ၊ သင်က အရမ်းမူးနေပြီ။",
        "I haven't done anything to you." => "ငါက သင့်ကို ဘာမှမလုပ်ခဲ့ဘူး။",
        "Get him out of here first." => "အရင် သူ့ကို ဒီကနေ ထုတ်ထားလိုက်ပါ။",
        "Just get him out." => "သူ့ကို ရှင်းထုတ်လိုက်ပါအောင်။",
        "You all need to talk to your staff!" => "သင်တို့အားလုံးက သင်တို့ဝန်ထမ်းတွေကို ပြောရမယ်!",
        "Are you okay?" => "သင်နေကောင်းရဲ့လား?",
        "Yes, Mama." => "ဟုတ်ကဲ့၊ မာမာ။",
        "She's just being so dramatic!" => "သူက အရမ်းအလွန်အကျွံလုပ်နေတာပဲ!",
        "She acts like she's a princess here." => "သူက ဒီမှာ်းသမီးလို့ လုပ်နေတာပဲ။",
        "Even I got my nipple bitten," => "ကျွန်တော်တောင် ရင်သပ်ကို ကိုက်ခံရပြီး၊",
        "and I just dealt with it." => "သာမန်အတိုင်း ဖြေရှင်းလိုက်တယ်။",
        "Well, at least I demanded" => "ကောင်းပြီ၊ အနည်းဆုံးတော့ ကျွန်တော်က",
        "a huge tip from him." => "သူ့ဆီက ငွေကြေးအများကြီး တောင်းခံခဲ့တယ်။",
        "Honestly, sometimes..." => "ရိုးသားစွာပြောရရင်၊ တစ်ခါတစ်ရံ...",
        "I get into fights because of all of you." => "သင်တို့အားလုံးကြောင့် ငါက ရန်ဖြစ်ရတယ်။",
        "Please, don't be too picky." => "ကျေးဇူးပြု၍ သိပ်မရွေးချယ်ပါနဲ့။",
        "Whatever the guests want," => "ဧည့်သည်တွေ ဘာလိုချင်လိုချင်၊",
        "you do it." => "သင်က လုပ်ပေးရမယ်။",
        "Just make up for it" => "အဲဒါကို ပဲ",
        "with the drinks. Right?" => "သောက်စရာတွေနဲ့ ဖြည့်ဆည်းပေးရမယ်။ မဟုတ်လား?",
        "How else are we going to" => "တစ်ခြားဘယ်လိုနည်းနဲ့ ကျွန်တော်တို့",
        "make the big money, huh?" => "ငွေကြေးအများကြီး ရှာမှာလဲ၊ ဟင်။",
        "That's right, Ma." => "မှန်ပါတယ်၊ မာမာ။",
        "Me, I'll do anything for the money!" => "ငါက၊ ပိုက်ဆံအတွက် ဘာမဆို လုပ်မယ်!",
        "It's up to us if we" => "ငါတို့က",
        "accept the guest." => "ဧည့်သည်ကို လက်ခံမလား ဆိုတာ ငါတို့အပေါ်မှာ မူတည်တယ်။",
        "We can tell just" => "ငါတို့က",
        "by looking at them." => "ကြည့်ရုံနဲ့ ပြောလို့ရတယ်။",
        "Maybe I should pinch all your nipples!" => "ငါက သင်တို့ရင်သပ်တွေ အားလုံးကို ကိုင်ကြည့်သင့်တယ်နော်!",
    ];
    
    // Check if we have a direct translation
    if (isset($specificPhrases[$english])) {
        return $specificPhrases[$english];
    }
    
    // For other phrases, return a placeholder (you can expand this with more translations)
    return "[MM: " . $english . "]";  // Placeholder - you'd need full translation service
}

// Read the input file
if (!file_exists($inputFile)) {
    die("Error: Input file not found: $inputFile\n");
}

$lines = file($inputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$output = [];

foreach ($lines as $line) {
    $translated = translateLine($line);
    $output[] = $translated;
}

// Write output file
file_put_contents($outputFile, implode("\n", $output));

echo "Translation complete!\n";
echo "Input: $inputFile\n";
echo "Output: $outputFile\n";
echo "Translated " . count($output) . " lines\n";

