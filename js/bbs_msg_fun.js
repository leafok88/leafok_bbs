/**
 * Checks/unchecks all tables
 *
 * @param   string   the form name
 * @param   boolean  whether to check or to uncheck the element
 *
 * @return  boolean  always true
 */
function setCheckboxes(the_form, do_check)
{
    var elts      = document.forms[the_form].elements['delete_msg_id[]'];
    var elts_cnt  = (typeof(elts.length) != 'undefined')
                  ? elts.length
                  : 0;

    if (elts_cnt) {
        for (var i = 0; i < elts_cnt; i++) {
            elts[i].checked = do_check;
        } // end for
    } else {
        elts.checked        = do_check;
    } // end if... else

    return true;
} // end of the 'setCheckboxes()' function
