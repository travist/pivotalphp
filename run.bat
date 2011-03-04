@echo off
FOR /F "tokens=*" %%i in ('cd') do SET batchDir=%%i
%~d0\php\php.exe "%batchDir%\run.php"
