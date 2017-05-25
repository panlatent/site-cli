# Site-cli shell configure
if [ `php -r "echo version_compare(PHP_VERSION, '5.5', '<');"` ]; then
    return
fi

# Site-cli completion function
function _site_cli_complete {

    local CMDLINE_CONTENTS="$COMP_LINE"
    local CMDLINE_CURSOR_INDEX="$COMP_POINT"
    local CMDLINE_WORDBREAKS="$COMP_WORDBREAKS";

    export CMDLINE_CONTENTS CMDLINE_CURSOR_INDEX CMDLINE_WORDBREAKS

    local RESULT STATUS;

    RESULT="$({% command %} _completion </dev/null)";
    STATUS=$?;

    local cur mail_check_backup;

    mail_check_backup=$MAILCHECK;
    MAILCHECK=-1;

    _get_comp_words_by_ref -n : cur;


    if [ $STATUS -eq 200 ]; then
        _filedir;
        return 0;

    elif [ $STATUS -ne 0 ]; then
        echo -e "$RESULT";
        return $?;
    fi;

    COMPREPLY=(`compgen -W "$RESULT" -- $cur`);

    __ltrim_colon_completions "$cur";

    MAILCHECK=mail_check_backup;
};

if [ "$(type -t _get_comp_words_by_ref)" == "function" ]; then
    complete -F _site_cli_complete "{% program %}";
else
    >&2 echo "Completion was not registered for {% program %}:";
    >&2 echo "The 'bash-completion' package is required but doesn't appear to be installed.";
fi
