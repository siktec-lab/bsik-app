@REM A windows wrapper for the BSIK CLI: bsikc.php

@echo off

@REM ::%cd% refers to the current working directory (variable)
@REM ::%~dp0 refers to the full path to the batch file's directory (static)
@REM ::%~dpnx0 and %~f0 both refer to the full path to the batch directory and file name (static).

SET DIR=%~dp0

@REM execte the CLI a php script with with name bsikc.php and all arguments passed to this batch file
php "%DIR%/bsikc.php" %*

@REM wait for the user to press a key before closing the window
@REM echo.
@REM echo Press any key to exit...
@REM pause >nul

@REM exit the batch file
exit /b


