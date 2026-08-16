@echo off
rem Lanzador local de soketi para probar el chat en tiempo real.
rem Requiere: soketi global instalado (npm i -g @soketi/soketi) y
rem Node 18 portable en la carpeta temporal (compatible con Node v24 del sistema).
rem Borrar: npm rm -g @soketi/soketi y borrar la carpeta node-v18.20.8-win-x64.

set SOKETI_DEFAULT_APP_ID=123456
set SOKETI_DEFAULT_APP_KEY=ABCABC12345
set SOKETI_DEFAULT_APP_SECRET=PROMOABCABC12345
set SOKETI_DEBUG=1

"C:\Users\ASUS\AppData\Local\Temp\opencode\soketi\node-v18.20.8-win-x64\node.exe" "%APPDATA%\npm\node_modules\@soketi\soketi\bin\server.js" start
