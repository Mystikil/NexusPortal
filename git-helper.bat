@echo off
setlocal ENABLEEXTENSIONS ENABLEDELAYEDEXPANSION

:: ---- sanity checks ----
where git >nul 2>nul || (echo [ERROR] Git not found in PATH. Install Git and retry.& goto :end)

where gh >nul 2>nul && set "GH=1" || set "GH=0"

:: ---- pick folder ----
set "TARGET="
set /p TARGET=Folder to work in (blank = current): 
if "%TARGET%"=="" set "TARGET=%cd%"
if not exist "%TARGET%" (echo [ERROR] Folder not found: %TARGET% & goto :end)

pushd "%TARGET%" >nul || (echo [ERROR] Cannot enter %TARGET% & goto :end)

:menu
cls
echo =======================================
echo   Git Helper - %cd%
echo =======================================
echo  0^) One-shot: init -> first commit -> create GH repo -> push
echo  1^) Init repo (main as default)
echo  2^) First commit (add all)
echo  3^) Link/Change remote "origin"
echo  4^) Create GitHub repo (uses gh CLI if available)
echo  5^) Push current branch (set upstream if needed)
echo  6^) Pull
echo  7^) Status + last 5 commits
echo  8^) Rename current branch to "main"
echo  9^) Show current remotes/branch
echo  Q^) Quit
echo.

:: Use CHOICE. IMPORTANT: check ERRORLEVELs in **descending** order!
choice /c 0123456789Q /n /m "Choose: "
set "OPT=%errorlevel%"

:: errorlevel mapping for /c 0..9Q  (1-based index):
:: 11=Q, 10=9, 9=8, 8=7, 7=6, 6=5, 5=4, 4=3, 3=2, 2=1, 1=0
if %OPT% GEQ 12 goto menu
if %OPT%==11 goto doQuit
if %OPT%==10 goto doShow
if %OPT%==9  goto doRenameMain
if %OPT%==8  goto doStatus
if %OPT%==7  goto doPull
if %OPT%==6  goto doPush
if %OPT%==5  goto doCreateGH
if %OPT%==4  goto doRemote
if %OPT%==3  goto doFirstCommit
if %OPT%==2  goto doInit
if %OPT%==1  goto doAll
goto menu

:doAll
call :doInit
call :doFirstCommit
call :doCreateGH
call :doPush
goto :pauseback

:doInit
echo.
echo [Init] Setting main as default branch...
git config init.defaultBranch main >nul 2>nul
git rev-parse --is-inside-work-tree >nul 2>nul && (
  echo Repo already exists here.
) || (
  git init -b main 2>nul || git init
)
goto :eof

:doFirstCommit
echo.
set "msg=Initial commit"
set /p msg=Commit message ^(default: Initial commit^): 
if "%msg%"=="" set "msg=Initial commit"
git add -A
git commit -m "%msg%" || echo (No changes to commit)
goto :eof

:doRemote
echo.
set /p REM=https URL for origin (e.g. https://github.com/USER/REPO.git): 
if "%REM%"=="" (echo [WARN] Skipping; no URL entered.& goto :eof)
git remote get-url origin >nul 2>nul && (
  git remote set-url origin "%REM%"
) || (
  git remote add origin "%REM%"
)
echo Origin set to: %REM%
goto :eof

:doCreateGH
echo.
if "%GH%"=="0" (
  echo [INFO] GitHub CLI not detected.
  echo Create the repo on GitHub, then run option 3 to set the remote.
  goto :eof
)
set /p ORG=GitHub owner/user (blank = your default): 
set /p NAME=Repository name (blank = current folder): 
for %%# in (.) do if "%NAME%"=="" set NAME=%%~nx#
set "VIS=--private"
set /p VISQ=Visibility [public/private] (default private): 
if /i "%VISQ%"=="public" set "VIS=--public"
if not "%ORG%"=="" (set "REPO=%ORG%/%NAME%") else (set "REPO=%NAME%")
echo Creating repo %REPO% ...
gh repo create "%REPO%" %VIS% --source . --remote origin --push -y || (
  echo [ERROR] gh failed. Create on GitHub manually and use option 3.
)
goto :eof

:doPush
for /f "delims=" %%B in ('git rev-parse --abbrev-ref HEAD 2^>nul') do set "BR=%%B"
if "%BR%"=="" set "BR=main"
git rev-parse --is-inside-work-tree >nul 2>nul || (echo Not a git repo.& goto :eof)
git remote get-url origin >nul 2>nul || (echo No 'origin' remote. Use option 3 or 4.& goto :eof)
echo Pushing branch %BR% ...
git push -u origin "%BR%"
goto :eof

:doPull
git pull --ff-only || git pull
goto :eof

:doStatus
git status
echo.
git --no-pager log --oneline -5
goto :eof

:doRenameMain
for /f "delims=" %%B in ('git rev-parse --abbrev-ref HEAD 2^>nul') do set "CUR=%%B"
if /i not "%CUR%"=="main" (
  git branch -M main || echo Make an initial commit first (option 2).
) else (
  echo Already on main.
)
goto :eof

:doShow
echo.
for /f "delims=" %%B in ('git rev-parse --abbrev-ref HEAD 2^>nul') do set "BR=%%B"
if "%BR%"=="" set "BR=(no branch)"
echo Current branch: %BR%
git remote -v
goto :eof

:pauseback
echo.
pause
goto :menu

:doQuit
popd >nul
echo Bye!
:end
endlocal
