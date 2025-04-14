<?php

// 대상 디렉토리 설정
$directories = ['app', 'resources', 'Modules'];

// 정규식 패턴 정의 - app()->tagged()를 사용하는 패턴 찾기
$pattern = '/\$([a-zA-Z0-9_]+)\s*=\s*app\(\)->tagged\([\'"]([a-zA-Z0-9_]+)[\'"]\);/';

// 새로운 패턴으로 대체
$replacement = function($matches) {
    $varName = $matches[1];
    $tagName = $matches[2];
    return "\$".$varName."Tagged = app()->tagged('$tagName');
\$".$varName." = null;
foreach (\$".$varName."Tagged as \$item) {
    \$".$varName." = \$item;
    break;
}";
};

// 파일 검색 및 수정 함수
function processDirectory($dir, $pattern, $replacement) {
    $modifiedFiles = [];
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);
            
            // 패턴이 있는지 확인
            if (preg_match($pattern, $content)) {
                // 내용 대체
                $newContent = preg_replace_callback($pattern, $replacement, $content);
                
                // 내용이 변경되었다면 파일 쓰기
                if ($newContent !== $content) {
                    file_put_contents($filePath, $newContent);
                    $modifiedFiles[] = $filePath;
                }
            }
        }
    }
    
    return $modifiedFiles;
}

// 스크립트 실행
$totalModifiedFiles = [];

foreach ($directories as $directory) {
    if (is_dir($directory)) {
        echo "디렉토리 처리 중: {$directory}\n";
        $modifiedFiles = processDirectory($directory, $pattern, $replacement);
        $totalModifiedFiles = array_merge($totalModifiedFiles, $modifiedFiles);
    } else {
        echo "디렉토리를 찾을 수 없음: {$directory}\n";
    }
}

// 결과 출력
echo "처리 완료!\n";
echo "수정된 파일 수: " . count($totalModifiedFiles) . "\n";

if (count($totalModifiedFiles) > 0) {
    echo "수정된 파일 목록:\n";
    foreach ($totalModifiedFiles as $file) {
        echo "- {$file}\n";
    }
}
