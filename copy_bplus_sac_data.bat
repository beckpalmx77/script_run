@echo off
setlocal enabledelayedexpansion

:: กำหนดโฟลเดอร์แหล่งข้อมูลและปลายทาง
set "source1=\\192.168.88.13\d\BackupBplus"
set "destination1=\\192.168.88.10\bplus\BackupBplus"

:: ดึงข้อมูลเดือนและปีปัจจุบัน
for /f "tokens=2-4 delims=/ " %%a in ('date /t') do (
    set "month=%%a"
    set "year=%%c"
)

:: แปลงชื่อเดือนเป็นตัวอักษร (jan, feb, etc.)
set "monthName="
for %%m in (01 jan 02 feb 03 mar 04 apr 05 may 06 jun 07 jul 08 aug 09 sep 10 oct 11 nov 12 dec) do (
    if "!month!"=="%%m" (
        set "monthName=%%m"
        goto :done
    )
)
:done

:: สร้างโฟลเดอร์ย่อยตามเดือน-ปี เช่น jan-2024
set "subfolder=%monthName%-%year%"
set "destination1=%destination1%\%subfolder%"

echo Copying files from %source1% to %destination1%...

:: ตรวจสอบและสร้างโฟลเดอร์ปลายทางถ้ายังไม่มี
if not exist "%destination1%" (
    mkdir "%destination1%"
)

:: คัดลอกไฟล์จาก source1 ไปยัง destination1
xcopy "%source1%\*" "%destination1%\" /E /H /C /I /Y
echo Files copied from %source1% to %destination1%.
echo.

:: คัดลอกไฟล์จากโฟลเดอร์ที่สอง
set "source2=D:\backups"
set "destination2=\\192.168.88.10\bplus\SAC_Backup"
set "destination2=%destination2%\%subfolder%"

echo Copying files from %source2% to %destination2%...

:: ตรวจสอบและสร้างโฟลเดอร์ปลายทางถ้ายังไม่มี
if not exist "%destination2%" (
    mkdir "%destination2%"
)

:: คัดลอกไฟล์จาก source2 ไปยัง destination2
xcopy "%source2%\*" "%destination2%\" /E /H /C /I /Y
echo Files copied from %source2% to %destination2%.

echo.
echo Copy process completed.

del /q "%source1%\*.*"
echo ลบไฟล์ใน %source1% เรียบร้อยแล้ว.

del /q "%source2%\*.*"
echo ลบไฟล์ใน %source2% เรียบร้อยแล้ว.