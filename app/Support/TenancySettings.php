<?php

namespace App\Support;

/**
 * Definitions for the generic Tenancy Settings screen (legacy mysql5 master tables).
 * Every table has: id, <fields>, status (A/X) and the created_/lastupdate_ audit columns.
 *
 * Field types: text | textarea | number | integer | time | site | doctype | department
 * Options: required, max, list (show in table), suggest (datalist of existing values), placeholder, help.
 */
class TenancySettings
{
    private static function f(string $name, string $label, string $type = 'text', array $o = []): array
    {
        return ['name' => $name, 'label' => $label, 'type' => $type] + $o + ['required' => false, 'list' => true];
    }

    public static function all(): array
    {
        return [
            'doctype' => [
                'label' => 'Doc Type', 'group' => 'Documents', 'icon' => 'fa-file-alt', 'table' => 'msdoctype',
                'desc' => 'Document types handled by Tenancy (SIK, SIFO, SIMKB, SIKE, ALL).',
                'unique' => ['doctype'],
                'fields' => [
                    self::f('doctype', 'Doc Type', 'text', ['required' => true, 'max' => 5, 'placeholder' => 'e.g. SIK']),
                    self::f('documentname', 'Document Name', 'text', ['max' => 50, 'placeholder' => 'e.g. Izin Kerja']),
                ],
            ],
            'worktype' => [
                'label' => 'Work Type', 'group' => 'Documents', 'icon' => 'fa-hard-hat', 'table' => 'msworktype',
                'desc' => 'Types of work per document type and site.',
                'fields' => [
                    self::f('doctype', 'Doc Type', 'doctype', ['required' => true]),
                    self::f('siteid', 'Site', 'site', ['required' => true]),
                    self::f('worktype', 'Work Type', 'text', ['required' => true, 'max' => 50, 'placeholder' => 'e.g. RENOVASI']),
                    self::f('worktypedescr', 'Description', 'text', ['max' => 100]),
                ],
            ],
            'workhour' => [
                'label' => 'Work Hour', 'group' => 'Documents', 'icon' => 'fa-clock', 'table' => 'msworkhour',
                'desc' => 'Allowed working hours per document type and site.',
                'fields' => [
                    self::f('doctype', 'Doc Type', 'doctype', ['required' => true]),
                    self::f('siteid', 'Site', 'site', ['required' => true]),
                    self::f('workhour', 'Work Hour', 'text', ['required' => true, 'max' => 100, 'placeholder' => 'e.g. Weekday Day']),
                    self::f('startworkhour', 'Start', 'time'),
                    self::f('endworkhour', 'End', 'time'),
                    self::f('workhournote', 'Note', 'textarea', ['list' => false]),
                ],
            ],
            'simkbtype' => [
                'label' => 'SIMKB Type', 'group' => 'Documents', 'icon' => 'fa-boxes', 'table' => 'mssimkbtype',
                'desc' => 'Goods in/out types per document type and site.',
                'fields' => [
                    self::f('doctype', 'Doc Type', 'doctype', ['required' => true]),
                    self::f('siteid', 'Site', 'site', ['required' => true]),
                    self::f('simkbtypeid', 'Type ID', 'text', ['required' => true, 'max' => 50]),
                    self::f('simkbtype', 'Type Name', 'text', ['max' => 100]),
                ],
            ],
            'gate' => [
                'label' => 'Entrance / Exit Gate', 'group' => 'Documents', 'icon' => 'fa-door-open', 'table' => 'msentranceexitgate',
                'desc' => 'Gates goods and workers may use, per site.',
                'fields' => [
                    self::f('doctype', 'Doc Type', 'doctype', ['required' => true]),
                    self::f('siteid', 'Site', 'site', ['required' => true]),
                    self::f('entranceexitgateid', 'Gate ID', 'text', ['required' => true, 'max' => 50]),
                    self::f('entranceexitgate', 'Gate Name', 'text', ['max' => 100]),
                    self::f('entrancetype', 'Gate Type', 'text', ['max' => 10, 'suggest' => true, 'placeholder' => 'e.g. in / out']),
                ],
            ],
            'daterestrict' => [
                'label' => 'Date Restrict', 'group' => 'Documents', 'icon' => 'fa-calendar-times', 'table' => 'msdaterestrict',
                'desc' => 'Minimum lead time rules for document dates.',
                'fields' => [
                    self::f('doctype', 'Doc Type', 'doctype', ['required' => true]),
                    self::f('siteid', 'Site', 'site', ['required' => true]),
                    self::f('restricttype', 'Restrict Type', 'text', ['required' => true, 'max' => 50, 'suggest' => true]),
                    self::f('intervalday', 'Interval (days)', 'integer'),
                    self::f('intervalmessage', 'Message', 'text', ['max' => 100]),
                ],
            ],
            'printnote' => [
                'label' => 'Print Note', 'group' => 'Documents', 'icon' => 'fa-print', 'table' => 'msprintnote',
                'desc' => 'Numbered notes printed on documents.',
                'fields' => [
                    self::f('doctype', 'Doc Type', 'doctype', ['required' => true]),
                    self::f('siteid', 'Site', 'site', ['required' => true]),
                    self::f('departmentid', 'Department', 'department', ['required' => true]),
                    self::f('nomor', 'No.', 'integer'),
                    self::f('note', 'Note', 'textarea'),
                ],
            ],
            'autonbr' => [
                'label' => 'Auto Number', 'group' => 'Documents', 'icon' => 'fa-hashtag', 'table' => 'msautonbr',
                'desc' => 'Running document numbers per prefix, year and month. Changing the counter changes the next number issued.',
                'fields' => [
                    self::f('doctype', 'Doc Type', 'doctype', ['required' => true]),
                    self::f('siteid', 'Site', 'site', ['required' => true]),
                    self::f('prefix', 'Prefix', 'text', ['required' => true, 'max' => 5]),
                    self::f('year', 'Year', 'text', ['required' => true, 'max' => 4]),
                    self::f('month', 'Month', 'text', ['required' => true, 'max' => 2]),
                    self::f('autonbr', 'Counter', 'integer', ['required' => true]),
                ],
            ],
            'usertypedocdept' => [
                'label' => 'User Type Doc / Dept', 'group' => 'Access & Approval', 'icon' => 'fa-user-lock', 'table' => 'msusertypedocdept',
                'desc' => 'Which documents and departments each user type can use, with its badge colour and landing URL.',
                'fields' => [
                    self::f('usertype', 'User Type', 'text', ['required' => true, 'max' => 50, 'suggest' => true]),
                    self::f('siteid', 'Site', 'site', ['required' => true]),
                    self::f('doctype', 'Doc Type', 'doctype', ['required' => true]),
                    self::f('departmentid', 'Department', 'department', ['required' => true]),
                    self::f('template', 'Badge Class', 'text', ['max' => 50, 'suggest' => true, 'placeholder' => 'e.g. bg-success']),
                    self::f('urlaccess', 'URL Access', 'text', ['max' => 50]),
                ],
            ],
            'approvalspecific' => [
                'label' => 'Approval Specific', 'group' => 'Access & Approval', 'icon' => 'fa-user-check', 'table' => 'msapprovalspecific',
                'desc' => 'Special approval rules that depend on a parameter value.',
                'fields' => [
                    self::f('doctype', 'Doc Type', 'doctype', ['required' => true]),
                    self::f('siteid', 'Site', 'site', ['required' => true]),
                    self::f('departmentid', 'Department', 'department'),
                    self::f('urutan', 'Order', 'number', ['required' => true]),
                    self::f('urutanawal', 'From Order', 'integer', ['list' => false]),
                    self::f('urutanakhir', 'To Order', 'integer', ['list' => false]),
                    self::f('username', 'Username(s)', 'text', ['required' => true, 'max' => 255, 'placeholder' => 'comma-separated']),
                    self::f('accesstype', 'Access Type', 'text', ['required' => true, 'max' => 10, 'suggest' => true]),
                    self::f('conditiontype', 'Condition', 'text', ['required' => true, 'max' => 50, 'suggest' => true]),
                    self::f('parameterstring', 'Parameter (text)', 'text', ['max' => 100, 'list' => false]),
                    self::f('parameterint', 'Parameter (number)', 'number', ['list' => false]),
                ],
            ],
            'globalsetting' => [
                'label' => 'Global Setting', 'group' => 'System', 'icon' => 'fa-sliders-h', 'table' => 'msglobalsetting',
                'desc' => 'Paper sizes, file upload limits, default tenant password, announcement and manual book.',
                'unique' => ['idparameter'],
                'fields' => [
                    self::f('idparameter', 'Parameter ID', 'text', ['required' => true, 'max' => 50]),
                    self::f('parameter', 'Value', 'textarea', ['max' => 255]),
                ],
            ],
            'mailsetting' => [
                'label' => 'Mail Setting', 'group' => 'System', 'icon' => 'fa-envelope', 'table' => 'msmailsetting',
                'desc' => 'Sender details and the link used in notification emails.',
                'fields' => [
                    self::f('from_email', 'From Email', 'text', ['required' => true, 'max' => 50]),
                    self::f('from_name', 'From Name', 'text', ['required' => true, 'max' => 50]),
                    self::f('link_url', 'Link URL', 'text', ['max' => 255]),
                ],
            ],
            'mailtemplate' => [
                'label' => 'Mail Template', 'group' => 'System', 'icon' => 'fa-envelope-open-text', 'table' => 'msmailtemplate',
                'desc' => 'Email subject and body per document type and action.',
                'fields' => [
                    self::f('doctype', 'Doc Type', 'doctype'),
                    self::f('action', 'Action', 'text', ['max' => 50, 'suggest' => true]),
                    self::f('subjectemail', 'Subject', 'text', ['max' => 255]),
                    self::f('bodyemail', 'Body', 'textarea', ['list' => false]),
                ],
            ],
            'faq' => [
                'label' => 'FAQ', 'group' => 'System', 'icon' => 'fa-question-circle', 'table' => 'msfaq',
                'desc' => 'Frequently asked questions shown to tenants.',
                'fields' => [
                    self::f('nomor', 'No.', 'integer'),
                    self::f('stringnomor', 'Label', 'text', ['max' => 25]),
                    self::f('question', 'Question', 'textarea'),
                    self::f('answer', 'Answer', 'textarea'),
                ],
            ],
        ];
    }

    public static function get(string $key): array
    {
        $all = self::all();
        abort_unless(isset($all[$key]), 404);

        return $all[$key];
    }
}
