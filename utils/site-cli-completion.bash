# Site-cli shell configure
if [ `php -r "echo version_compare(PHP_VERSION, '5.5', '<');"` ]; then
    return
fi

# Site-cli complete

function _site_a4f19242e1c1c42a_complete {
    local -x CMDLINE_CONTENTS="$words"
    local -x CMDLINE_CURSOR_INDEX
    (( CMDLINE_CURSOR_INDEX = ${#${(j. .)words[1,CURRENT]}} ))

    local RESULT STATUS
    RESULT=("${(@f)$( /usr/local/bin/site _completion )}")
    STATUS=$?;


    if [ $STATUS -eq 200 ]; then
        _path_files;
        return 0;

    elif [ $STATUS -ne 0 ]; then
        echo -e "$RESULT";
        return $?;
    fi;

    compadd -- $RESULT
};

compdef _site_a4f19242e1c1c42a_complete "/usr/local/bin/site";