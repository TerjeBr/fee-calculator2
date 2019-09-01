@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../vendor/league/html-to-markdown/bin/html-to-markdown
php "%BIN_TARGET%" %*
