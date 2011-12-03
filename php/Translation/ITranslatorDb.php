<?php

namespace Translation;

interface ITranslatorDb
{
    const T_TRANS_KEYS = 'dic_translation_keys';
    const D_ID_DIC = 'id_dic';
    const D_KEY = 'key';

    const T_TRANS = 'dic_translation';
    const O_ID_TRANS = 'id_translation';
    const O_ID_DIC = 'id_dic';
    const O_ID_LANG = 'id_lang';
    const O_WORD = 'word';

    const T_GROUP = 'dic_group';
    const G_ID_GROUP = 'id_group';
    const G_GROUP = 'group';

    const T_LANG = 'dic_lang';
    const L_ID_LANG = 'id_lang';
    const L_LANG = 'lang';
    const L_DECLENSION = 'declension';

    const T_MERGE_GROUP = 'dic_merge_group';
    const M_ID_DIC = 'id_dic';
    const M_ID_GROUP = 'id_group';
}
