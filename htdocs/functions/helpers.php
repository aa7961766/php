<?php
// 安全输出 HTML
function safe_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}    