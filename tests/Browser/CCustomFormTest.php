<?php

namespace Exceedone\Exment\Tests\Browser;

use Exceedone\Exment\Model\CustomForm;

class CCustomFormTest extends ExmentKitTestCase
{
    use ExmentKitPrepareTrait;

    /**
     * pre-excecute process before test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->login();
    }

    /**
     * prepare test table.
     */
    public function testPrepareTestTable() {
        $this->createCustomTable('exmenttest_form');
        $this->createCustomTable('exmenttest_form_relation');
    }

    /**
     * prepare test columns.
     */
    public function testPrepareTestColumn() {
        $targets = ['integer', 'text', 'datetime', 'select', 'boolean', 'yesno', 'image'];
        $this->createCustomColumns('exmenttest_form', $targets);
    }

    /**
     * prepare test columns and relation.
     */
    public function testPrepareTestColumnAndRelation() {
        $targets = ['integer', 'text', 'datetime', 'select', 'boolean', 'yesno', 'image'];
        $this->createCustomColumns('exmenttest_form_relation', $targets);
        $this->createCustomRelation('exmenttest_form', 'exmenttest_form_relation');
    }

    /**
     * Check custom form display.
     */
    public function testDisplayFormSetting()
    {
        // Check custom column form
        $this->visit('/admin/form/exmenttest_form')
                ->seePageIs(admin_url('form/exmenttest_form'))
                ->see('カスタムフォーム設定')
                ->seeInElement('th', 'テーブル名(英数字)')
                ->seeInElement('th', 'テーブル表示名')
                ->seeInElement('th', 'フォーム表示名')
                ->seeInElement('th', '操作')
                ->visit('/admin/form/exmenttest_form/create')
                ->seeInElement('h1', 'カスタムフォーム設定')
                ->seeInElement('label', 'フォーム表示名')
                ->seeInElement('h3[class=box-title]', 'ヘッダー基本設定')
                ->seeInElement('h3[class=box-title]', 'テーブル - Exmenttest Form')
                ->seeInElement('h3[class=box-title]', '子テーブル - Exmenttest Form Relation')
                ->seeInElement('label', 'フォームブロック名')
                ->seeInElement('h4', 'フォーム項目')
                ->seeInElement('h5', 'フォーム項目 列1')
                ->seeInElement('h5', 'フォーム項目 列2')
                ->seeInElement('h5', 'フォーム項目 候補一覧')
                ->seeInElement('h5', 'フォーム項目 候補一覧')
                // ->seeInElement('span[class=item-label]', 'ID')
                // ->seeInElement('span[class=item-label]', '内部ID(20桁)')
                ->seeInElement('span', 'Integer')
                ->seeInElement('span', 'One Line Text')
                ->seeInElement('span', 'Date and Time')
                ->seeInElement('span', 'Select From Static Value')
                ->seeInElement('span', 'Select 2 value')
                ->seeInElement('span', 'Yes No')
                ->seeInElement('span', 'Image')
                // ->seeInElement('span[class=item-label]', '作成日時')
                // ->seeInElement('span[class=item-label]', '更新日時')
                // ->seeInElement('span[class=item-label]', '作成ユーザー')
                // ->seeInElement('span[class=item-label]', '更新ユーザー')
                ->seeInElement('span', '見出し')
                ->seeInElement('span', '説明文')
                ->seeInElement('span', 'HTML')
            ;
    }

    /**
     * Create custom form.
     */
    public function testAddFormSuccess()
    {
        $pre_cnt = CustomForm::count();

        // Create custom form
        $this->visit('/admin/form/exmenttest_form/create')
                ->type('新しいフォーム', 'form_view_name')
                ->press('admin-submit')
                ->seePageIs(admin_url('form/exmenttest_form'))
                ->seeInElement('td', '新しいフォーム')
                ->assertEquals($pre_cnt + 1, CustomForm::count())
                ;

        $raw = CustomForm::orderBy('created_at', 'desc')->first();
        $id = array_get($raw, 'id');

        // Update custom form
        $this->visit('/admin/form/exmenttest_form/'. $id . '/edit')
                ->seeInField('form_view_name', '新しいフォーム')
                ->type('更新したフォーム', 'form_view_name')
                ->press('admin-submit')
                ->seePageIs(admin_url('form/exmenttest_form'))
                ->seeInElement('td', '更新したフォーム');
    }


    /**
     * A Dusk test example.
     *
     * @return void
     */
    // precondition : login success
//     public function testLoginSuccessWithTrueUsername()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/auth/login')
//                 ->type('username', 'testuser')
//                 ->type('password', 'test123456')
//                 ->press('Login')
//                 ->waitForText('Login successful')
//                 ->assertPathIs('/admin')
//                 ->assertTitle('Dashboard')
//                 ->assertSee('Dashboard');
//         });
//     }

//     // AutoTest_Form_01
//     public function testCreateTable1()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/table')
//                 ->waitForText('New')
//                 ->clickLink('New')
//                 ->pause(5000)
//                 ->type('table_name', 'exmenttest_form')
//                 ->type('table_view_name', 'EXMENT Form')
//                 ->type('description', 'EXMENT Test table')
//                 ->type('color', '#ff0000')
//                 ->type('icon', 'fa-automobile')
//                 ->click('.fa.fa-automobile');
//             $browser->script('document.querySelector(".search_enabled.la_checkbox").click();');
//             $browser->press('Submit')
//                 ->pause(5000)
//                 ->assertMissing('.has-error')
//                 ->assertPathIs('/admin/table')
//                 ->assertSee('exmenttest_form')
//                 ->assertSee('EXMENT Form');
//         });
//     }

//     // AutoTest_Form_02
//     public function testAddIntegerColumnTable1()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/column/exmenttest_form/create')
//                 ->type('column_name', 'integer')
//                 ->type('column_view_name', 'Integer')
//                 ->select('column_type', 'integer')
//                 ->type('options[number_min]', '10')
//                 ->type('options[number_max]', '100');
//             $browser->script('document.querySelector(".options_number_format.la_checkbox").click();');
//             $browser->press('Submit')
//                 ->pause(5000)
//                 ->assertPathIs('/admin/column/exmenttest_form')
//                 ->assertSee('integer');
//         });
//     }

//     // AutoTest_Form_03
//     public function testAddOneLineTextColumnTable1()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/column/exmenttest_form/create')
//                 ->type('column_name', 'onelinetext')
//                 ->type('column_view_name', 'One Line Text')
//                 ->select('column_type', 'text')
//                 ->type('options[string_length]', '256')
//                 ->click('#available_characters  label.checkbox-inline:nth-child(1) div.icheckbox_minimal-blue')
//                 ->press('Submit')
//                 ->pause(5000)
//                 ->assertPathIs('/admin/column/exmenttest_form')
//                 ->assertSee('onelinetext');
//         });
//     }

//     //     AutoTest_Form_04
//     public function testAddDateAndTimeColumnTable1()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/column/exmenttest_form/create')
//                 ->type('column_name', 'dateandtime')
//                 ->type('column_view_name', 'Date and Time')
//                 ->select('column_type', 'datetime')
//                 ->press('Submit')
//                 ->pause(5000)
//                 ->assertPathIs('/admin/column/exmenttest_form')
//                 ->assertSee('dateandtime');
//         });
//     }

//     //     AutoTest_Form_05
//     public function testAddSelectFromStaticValueColumnTable1()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/column/exmenttest_form/create')
//                 ->type('column_name', 'selectfromstaticvalue')
//                 ->type('column_view_name', "Select Froom Static Value")
//                 ->select('column_type', 'select')
//                 ->keys('.form-control.options_select_item', 'value1', '{ENTER}', 'value2');
//             $browser->script('document.querySelector(".options_multiple_enabled.la_checkbox").click();');
//             $browser->press('Submit')
//                 ->pause(5000)
//                 ->assertPathIs('/admin/column/exmenttest_form')
//                 ->assertSee('selectfromstaticvalue');
//         });
//     }

//     //     AutoTest_Form_06
//     public function testAddSelect2ValueColumnTable1()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/column/exmenttest_form/create')
//                 ->type('column_name', 'select2value')
//                 ->type('column_view_name', "Select 2 value")
//                 ->select('column_type', 'boolean')
//                 ->type('options[true_value]', "value1")
//                 ->type('options[true_label]', "label1")
//                 ->type('options[false_value]', "value2")
//                 ->type('options[false_label]', "label2");
//             $browser->script('document.querySelector(".options_multiple_enabled.la_checkbox").click();');
//             $browser->press('Submit')
//                 ->pause(5000)
//                 ->assertPathIs('/admin/column/exmenttest_form')
//                 ->assertSee('select2value');
//         });
//     }

//     // AutoTest_Form_07
//     public function testAddYesNoColumnTable1()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/column/exmenttest_form/create')
//                 ->type('column_name', 'yesno')
//                 ->type('column_view_name', 'Yes No')
//                 ->select('column_type', 'yesno')
//                 ->press('Submit')
//                 ->pause(5000)
//                 ->assertPathIs('/admin/column/exmenttest_form')
//                 ->assertSee('yesno');
//         });
//     }

//     // AutoTest_Form_08
//     public function testAddImageColumnTable1()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/column/exmenttest_form/create')
//                 ->type('column_name', 'image')
//                 ->type('column_view_name', 'Image')
//                 ->select('column_type', 'image');
//             $browser->script('document.querySelector(".options_multiple_enabled.la_checkbox").click();');
//             $browser->press('Submit')
//                 ->pause(5000)
//                 ->assertPathIs('/admin/column/exmenttest_form')
//                 ->assertSee('image');
//         });
//     }

//     // AutoTest_Form_09
//     public function testCreateTable2()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/table')
//                 ->waitForText('New')
//                 ->clickLink('New')
//                 ->pause(5000)
//                 ->type('table_name', 'exmenttest_form_relation')
//                 ->type('table_view_name', 'EXMENT Form Relation')
//                 ->type('description', 'EXMENT Test table')
//                 ->type('color', '#ff0000')
//                 ->type('icon', 'fa-automobile')
//                 ->click('.fa.fa-automobile');
//             $browser->script('document.querySelector(".search_enabled.la_checkbox").click();');
//             $browser->press('Submit')
//                 ->pause(5000)
//                 ->assertMissing('.has-error')
//                 ->assertPathIs('/admin/table')
//                 ->assertSee('exmenttest_form_relation')
//                 ->assertSee('EXMENT Form Relation');
//         });
//     }

//     // AutoTest_Form_10
//     public function testAddIntegerColumnTable2()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/column/exmenttest_form_relation/create')
//                 ->type('column_name', 'integer')
//                 ->type('column_view_name', 'Integer')
//                 ->select('column_type', 'integer')
//                 ->type('options[number_min]', '10')
//                 ->type('options[number_max]', '100');
//             $browser->script('document.querySelector(".options_number_format.la_checkbox").click();');
//             $browser->press('Submit')
//                 ->pause(5000)
//                 ->assertPathIs('/admin/column/exmenttest_form_relation')
//                 ->assertSee('integer');
//         });
//     }

//     // AutoTest_Form_11
//     public function testAddOneLineTextColumnTable2()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/column/exmenttest_form_relation/create')
//                 ->type('column_name', 'onelinetext')
//                 ->type('column_view_name', 'One Line Text')
//                 ->select('column_type', 'text')
//                 ->type('options[string_length]', '256')
//                 ->click('#available_characters  label.checkbox-inline:nth-child(1) div.icheckbox_minimal-blue')
//                 ->press('Submit')
//                 ->pause(5000)
//                 ->assertPathIs('/admin/column/exmenttest_form_relation')
//                 ->assertSee('onelinetext');
//         });
//     }

//     //     AutoTest_Form_12
//     public function testAddDateAndTimeColumnTable2()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/column/exmenttest_form_relation/create')
//                 ->type('column_name', 'dateandtime')
//                 ->type('column_view_name', 'Date and Time')
//                 ->select('column_type', 'datetime')
//                 ->press('Submit')
//                 ->pause(5000)
//                 ->assertPathIs('/admin/column/exmenttest_form_relation')
//                 ->assertSee('dateandtime');
//         });
//     }

//     //     AutoTest_Form_13
//     public function testAddSelectFromStaticValueColumnTable2()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/column/exmenttest_form_relation/create')
//                 ->type('column_name', 'selectfromstaticvalue')
//                 ->type('column_view_name', "Select Froom Static Value")
//                 ->select('column_type', 'select')
//                 ->keys('.form-control.options_select_item', 'value1', '{ENTER}', 'value2');
//             $browser->script('document.querySelector(".options_multiple_enabled.la_checkbox").click();');
//             $browser->press('Submit')
//                 ->pause(5000)
//                 ->assertPathIs('/admin/column/exmenttest_form_relation')
//                 ->assertSee('selectfromstaticvalue');
//         });
//     }

//     //     AutoTest_Form_14
//     public function testAddSelect2ValueColumnTable2()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/column/exmenttest_form_relation/create')
//                 ->type('column_name', 'select2value')
//                 ->type('column_view_name', "Select 2 value")
//                 ->select('column_type', 'boolean')
//                 ->type('options[true_value]', "value1")
//                 ->type('options[true_label]', "label1")
//                 ->type('options[false_value]', "value2")
//                 ->type('options[false_label]', "label2");
//             $browser->script('document.querySelector(".options_multiple_enabled.la_checkbox").click();');
//             $browser->press('Submit')
//                 ->pause(5000)
//                 ->assertPathIs('/admin/column/exmenttest_form_relation')
//                 ->assertSee('select2value');
//         });
//     }

//     // AutoTest_Form_15
//     public function testAddYesNoColumnTable2()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/column/exmenttest_form_relation/create')
//                 ->type('column_name', 'yesno')
//                 ->type('column_view_name', 'Yes No')
//                 ->select('column_type', 'yesno')
//                 ->press('Submit')
//                 ->pause(5000)
//                 ->assertPathIs('/admin/column/exmenttest_form_relation')
//                 ->assertSee('yesno');
//         });
//     }

//     // AutoTest_Form_16
//     public function testAddImageColumnTable2()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/column/exmenttest_form_relation/create')
//                 ->type('column_name', 'image')
//                 ->type('column_view_name', 'Image')
//                 ->select('column_type', 'image');
//             $browser->script('document.querySelector(".options_multiple_enabled.la_checkbox").click();');
//             $browser->press('Submit')
//                 ->pause(5000)
//                 ->assertPathIs('/admin/column/exmenttest_form_relation')
//                 ->assertSee('image');
//         });
//     }

//     // AutoTest_Form_17
//     public function testAddRelationOneToMany()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/relation/exmenttest_form/create')
//                 ->pause(5000);
//             $browser->script('$(".child_custom_table_id").val($("option").filter(function() {
//   return $(this).text() === "EXMENT Form Relation";
// }).first().attr("value")).trigger("change.select2")');
//             $browser->select('relation_type', 'one_to_many')
//                 ->press('Submit')
//                 ->waitForText('Save succeeded !')
//                 ->assertSeeIn('.table-hover tr:last-child td:nth-child(5)', 'EXMENT Form Relation')
//                 ->assertSeeIn('.table-hover tr:last-child td:nth-child(6)', 'One to Many')
//                 ->assertPathIs('/admin/relation/exmenttest_form');
//         });
//     }

//     // AutoTest_Form_18
//     public function testDisplayRelationSetting()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/table')
//                 ->assertSee('EXMENT Form');
//             $browser->script('$(".table-hover td").filter(function(){return $.trim($(this).text()) == "EXMENT Form"}).closest("tr").find("ins.iCheck-helper").click();');
//             $browser->press('Change Page')
//                 ->clickLink('Form Setting')
//                 ->pause(5000)
//                 ->assertSee('Custom Form Setting')
//                 ->assertSee('Define the form display that the user can enter. You can switch between role and users.')
//                 ->assertSee('Showing 1 to 1 of 1 entries')
//                 ->assertPathIs('/admin/form/exmenttest_form');
//         });
//     }

//     // AutoTest_Form_19
//     public function testDisplayCreateFormScreen()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/form/exmenttest_form')
//                 ->waitForText('New')
//                 ->clickLink('New')
//                 ->pause(5000);
//             $browser->with('form div:nth-child(1)', function ($block1) {
//                 $block1->assertSeeIn('div.box-header .box-title', 'Form Basic Setting')
//                     ->assertSeeIn('div.box-body .form-horizontal', 'Form View Name');
//             });
//             $browser->with('form div:nth-child(2)', function ($block2) {
//                 $block2->assertSeeIn('div.box-header .box-title', 'Table');
//                 $block2->assertSeeIn('div.box-body .form-inline', 'Form Block Name');
//                 $block2->with('div.box-body div[id*="items_default"]', function ($block_item) {
//                     $block_item->assertSeeIn('h5', 'Items')
//                         ->assertMissing('ul li');
//                 });
//                 $block2->with('div.box-body div[id*="suggests_default"]', function ($block_suggest) {
//                     $block_suggest->with('div:nth-child(1)', function ($block_suggest_column) {
//                         $block_suggest_column->assertSeeIn('h5', 'Table Column')
//                             ->assertVisible('ul li')
//                             ->assertSeeIn('ul', 'Integer')
//                             ->assertSeeIn('ul', 'One Line Text')
//                             ->assertSeeIn('ul', 'Date and Time')
//                             ->assertSeeIn('ul', 'Select Froom Static Value')
//                             ->assertSeeIn('ul', 'Select 2 value')
//                             ->assertSeeIn('ul', 'Yes No')
//                             ->assertSeeIn('ul', 'Image');
//                     });
//                     $block_suggest->with('div:nth-child(2)', function ($block_suggest_other) {
//                         $block_suggest_other->assertSeeIn('h5', 'Other')
//                             ->assertVisible('ul li')
//                             ->assertSeeIn('ul', 'Label')
//                             ->assertSeeIn('ul', 'Explain')
//                             ->assertSeeIn('ul', 'HTML');
//                     });
//                 });
//             });
//             $browser->with('form div:nth-child(3)', function ($block3) {
//                 $block3->assertSeeIn('div.box-header .box-title', 'Child Table - EXMENT Form Relation')
//                     ->assertSeeIn('div.box-body', 'Available')
//                     ->assertMissing('div.box-body div.checked');;
//             });
//         });
//     }

//     // AutoTest_Form_20
//     public function testEditFormSuccess()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/form/exmenttest_form')
//                 ->assertSee('EXMENT Form');
//             $browser->script('$(".table-hover td").filter(function(){return $.trim($(this).text()) == "EXMENT Form"}).closest("tr").click();');
//             $browser->pause(5000)
//                 ->type('form_view_name', 'EXMENT Form View Test')
//                 ->keys('div.box-custom_form_block:nth-child(2) input[name*="form_block_view_name"]', 'EXMENT Form Block Test')
//                 ->press('Add All Items')
//                 ->press('Submit')
//                 ->pause(5000)
//                 ->assertPathIs('/admin/form/exmenttest_form')
//                 ->waitForText('Save succeeded !')
//                 ->assertSeeIn('.table-hover tr:first-child td:nth-child(3)', 'EXMENT Form')
//                 ->assertSeeIn('.table-hover tr:first-child td:nth-child(4)', 'Form');
//         });
//     }

//     // AutoTest_Form_21
//     public function testDisplayData()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/data/exmenttest_form')
//                 ->assertSee('EXMENT Form')
//                 ->assertSee('EXMENT Test table')
//                 ->assertSee('Showing to of 0 entries');
//         });
//     }

//     // AutoTest_Form_22
//     public function testCreateRecord1()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/data/exmenttest_form/create')
//                 ->type('value[integer]', 100)
//                 ->type('value[onelinetext]', "EXMENT Test")
//                 ->keys('input[name*="value[dateandtime]"]', "2018-09-25", '{ENTER}')
//                 ->click('#embed-value input.select2-search__field')
//                 ->click('ul.select2-results__options li:first-child');
//             $browser->script('document.querySelector(".value_yesno.la_checkbox").click();');
//             $browser->script('document.querySelector(".value_select2value.la_checkbox").click();');
//             $browser->press('Submit')
//                 ->pause(5000)
//                 ->assertSee('Showing 1 to 1 of 1 entries');
//         });
//     }
// //    // AutoTest_Form_22
// //    public function testCreateRecord1()
// //    {
// //        $this->browse(function (Browser $browser) {
// //            $browser->visit('/admin/data/exmenttest_form/create')
// //                ->type('value[integer]', 100)
// //                ->type('value[onelinetext]', "EXMENT Test")
// //                ->keys('input[name*="value[dateandtime]"]', "2018-09-25", '{ENTER}')
// //                ->click('#embed-value input.select2-search__field')
// //                ->click('ul.select2-results__options li:first-child');
// //            $browser->script('document.querySelector(".value_yesno.la_checkbox").click();');
// //            $browser->script('document.querySelector(".value_select2value.la_checkbox").click();');
// //        });
// //    }

//     //	AutoTest_Form_23
//     public function testCreateRecord2()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/data/exmenttest_form/create')
//                 ->type('value[integer]', 99)
//                 ->type('value[onelinetext]', "EXMENT Test 2")
//                 ->keys('input[name*="value[dateandtime]"]', "2018-09-24", '{ENTER}')
//                 ->click('#embed-value input.select2-search__field')
//                 ->click('ul.select2-results__options li:last-child');
// //                ->attach('name*="value[image]"', 'C:\Users\admin\Pictures\background\real.jpg');
//         });
//     }

//     //	AutoTest_Form_24
//     public function testEditFormScreen()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/form/exmenttest_form')
//                 ->assertSee('EXMENT Form View Test');
//             $browser->script('$(".table-hover td").filter(function(){return $.trim($(this).text()) == "EXMENT Form View Test Edited"}).closest("tr").click();');
//             $browser->pause(5000)
//                 ->with('form div:nth-child(1)', function ($block1) {
//                     $block1->type('form_view_name', 'EXMENT Form View Test Edited');
//                 });
//             $browser->with('form div:nth-child(2)', function ($block2) {
//                 $block2->keys('div.box-body .form-inline input[name*="form_block_view_name"]', ['{CONTROL}', 'a'], 'EXMENT Form Block Test Edited');
//                 $block2->with('div.box-body div[id*="items_default"]', function ($block_item) {
//                     $block_item->click('ul li:first-child > a')->pause(2000)
//                         ->click('ul li:nth-child(2) > a')->pause(2000)
//                         ->click('ul li:nth-child(3) > a')->pause(2000)
//                         ->click('ul li:nth-child(4) > a')->pause(2000)
//                         ->click('ul li:nth-child(5) > a')->pause(2000)
//                         ->click('ul li:nth-child(6) > a')->pause(2000)
//                         ->click('ul li:nth-child(7) > a')->pause(2000);
//                 });

//                 $block2->with('div.box-body div[id*="suggests_default"]', function ($block_suggest) {
//                     $block_suggest->with('div:nth-child(1)', function ($block_suggest_column) {
//                         $block_suggest_column->assertSeeIn('h5', 'Table Column')
//                             ->assertVisible('ul li')
//                             ->assertSeeIn('ul', 'Integer')
//                             ->assertSeeIn('ul', 'One Line Text')
//                             ->assertSeeIn('ul', 'Date and Time')
//                             ->assertSeeIn('ul', 'Select Froom Static Value')
//                             ->assertSeeIn('ul', 'Select 2 value')
//                             ->assertSeeIn('ul', 'Yes No')
//                             ->assertSeeIn('ul', 'Image');
//                     });
//                     $block_suggest->with('div:nth-child(2)', function ($block_suggest_other) {
//                         $block_suggest_other->assertSeeIn('h5', 'Other')
//                             ->assertVisible('ul li')
//                             ->assertSeeIn('ul', 'Label')
//                             ->assertSeeIn('ul', 'Explain')
//                             ->assertSeeIn('ul', 'HTML');
//                     });
//                 });
//                 $block2->drag('div.box-body div[id*="suggests_default"] div:nth-child(1) ul li:nth-child(1)', 'div.box-body div[id*="items_default"] ul');
//                 $block2->drag('div.box-body div[id*="suggests_default"] div:nth-child(1) ul li:nth-child(1)', 'div.box-body div[id*="items_default"] ul');
//                 $block2->drag('div.box-body div[id*="suggests_default"] div:nth-child(1) ul li:nth-child(1)', 'div.box-body div[id*="items_default"] ul');
//                 $block2->drag('div.box-body div[id*="suggests_default"] div:nth-child(1) ul li:nth-child(1)', 'div.box-body div[id*="items_default"] ul');
//                 $block2->drag('div.box-body div[id*="suggests_default"] div:nth-child(1) ul li:nth-child(1)', 'div.box-body div[id*="items_default"] ul');
//                 $block2->drag('div.box-body div[id*="suggests_default"] div:nth-child(1) ul li:nth-child(1)', 'div.box-body div[id*="items_default"] ul');
//                 $block2->drag('div.box-body div[id*="suggests_default"] div:nth-child(1) ul li:nth-child(1)', 'div.box-body div[id*="items_default"] ul');
//             });
//             $browser->press('Submit');
//         });
//     }

//     // AutoTest_Form_25
//     public function testCreateRecord()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/data/exmenttest_form/create')
//                 ->type('value[integer]', 100)
//                 ->type('value[onelinetext]', "EXMENT Test")
//                 ->keys('input[name*="value[dateandtime]"]', "2018-09-25", '{ENTER}')
//                 ->click('#embed-value input.select2-search__field')
//                 ->click('ul.select2-results__options li:first-child');
//             $browser->script('document.querySelector(".value_yesno.la_checkbox").click();');
//             $browser->script('document.querySelector(".value_select2value.la_checkbox").click();');
//             $browser->press('Submit')
//                 ->pause(5000)
//                 ->waitForText('Save succeeded !')
//                 ->assertPathIs('/admin/data/exmenttest_form');
//         });
//     }

//     //  AutoTest_Form_26
//     public function testEditFormRealationSuccess()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/form/exmenttest_form')
//                 ->assertSee('EXMENT Form');
//             $browser->script('$(".table-hover td").filter(function(){return $.trim($(this).text()) == "EXMENT Form View Test Edited"}).closest("tr").click();');
//             $browser->pause(5000);
//             $browser->with('form div:nth-child(3)', function ($block3) {
//                 $block3->click('div.box-body div:nth-child(1) .iCheck-helper')
//                     ->press('Add All Items');
//             });
//             $browser->press('Submit')
//                 ->pause(5000)
//                 ->assertPathIs('/admin/form/exmenttest_form')
//                 ->waitForText('Save succeeded !')
//                 ->assertSeeIn('.table-hover tr:first-child td:nth-child(3)', 'EXMENT Form')
//                 ->assertSeeIn('.table-hover tr:first-child td:nth-child(4)', 'EXMENT Form View Test Edited');
//         });
//     }

//     // AutoTest_Form_27
//     public function testColumnRelationTable()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/form/exmenttest_form');
//             $browser->script('$(".table-hover td").filter(function(){return $.trim($(this).text()) == "EXMENT Form View Test Edited"}).closest("tr").click();');
//             $browser->pause(5000);
//             $browser->with('form div:nth-child(3)', function ($block3) {
//                 $block3->with('div:nth-child(2)', function ($block_item) {
//                     $block_item->assertVisible('ul li')
//                         ->assertSeeIn('ul', 'Integer')
//                         ->assertSeeIn('ul', 'One Line Text')
//                         ->assertSeeIn('ul', 'Date and Time')
//                         ->assertSeeIn('ul', 'Select Froom Static Value')
//                         ->assertSeeIn('ul', 'Select 2 value')
//                         ->assertSeeIn('ul', 'Yes No')
//                         ->assertSeeIn('ul', 'Image');
//                 });
//             });
//         });
//     }

//     // AutoTest_Form_28
//     public function testCheckViewOnlyOneLineTextColumn()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/form/exmenttest_form');
//             $browser->script('$(".table-hover td").filter(function(){return $.trim($(this).text()) == "EXMENT Form View Test Edited"}).closest("tr").click();');
//             $browser->pause(5000);
//             $browser->with('form div:nth-child(2)', function ($block2) {
//                 $block2->with('div.box-body div[id*="items_default"]', function ($block_item) {
//                     $block_item->click('ul li:nth-child(4) div.form-horizontal div:nth-child(1) ins.iCheck-helper');
//                 });


//             });
//             $browser->press('Submit')
//                 ->pause(2000);
//             $browser->visit('/admin/data/exmenttest_form/create')
//                 ->with('form div.embed-value-form.fields-group div:nth-child(4)', function ($rowOneLineText) {
//                     $rowOneLineText->assertVisible('input[readonly]');
//                 });
//         });
//     }

//     // AutoTest_Form_29
//     public function testUnCheckViewOnlyOneLineTextColumn()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/form/exmenttest_form');
//             $browser->script('$(".table-hover td").filter(function(){return $.trim($(this).text()) == "EXMENT Form View Test Edited"}).closest("tr").click();');
//             $browser->pause(5000);
//             $browser->with('form div:nth-child(2)', function ($block2) {
//                 $block2->with('div.box-body div[id*="items_default"]', function ($block_item) {
//                     $block_item->click('ul li:nth-child(4) div.form-horizontal div:nth-child(1) ins.iCheck-helper');
//                 });
//             });
//             $browser->press('Submit')
//                 ->pause(2000);
//             $browser->visit('/admin/data/exmenttest_form/create')
//                 ->with('form div.embed-value-form.fields-group div:nth-child(4)', function ($rowOneLineText) {
//                     $rowOneLineText->assertMissing('input[readonly]');
//                 });
//         });
//     }

//     // AutoTest_Form_30
//     public function testCheckHiddenFieldDateAndTimeColumn()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/form/exmenttest_form');
//             $browser->script('$(".table-hover td").filter(function(){return $.trim($(this).text()) == "EXMENT Form View Test Edited"}).closest("tr").click();');
//             $browser->pause(5000);
//             $browser->with('form div:nth-child(2)', function ($block2) {
//                 $block2->with('div.box-body div[id*="items_default"]', function ($block_item) {
//                     $block_item->click('ul li:nth-child(6) div.form-horizontal div:nth-child(2) ins.iCheck-helper');
//                 });
//             });
//             $browser->press('Submit')
//                 ->pause(2000);
//             $browser->visit('/admin/data/exmenttest_form/create')
//                 ->with('form div.embed-value-form.fields-group div:nth-child(6)', function ($rowDateAndTime) {
//                     $rowDateAndTime->assertDontSee('Date and Time');
//                 });
//         });
//     }

//     // AutoTest_Form_31
//     public function testUnCheckHiddenFieldDateAndTimeColumn()
//     {
//         $this->browse(function (Browser $browser) {
//             $browser->visit('/admin/form/exmenttest_form');
//             $browser->script('$(".table-hover td").filter(function(){return $.trim($(this).text()) == "EXMENT Form View Test Edited"}).closest("tr").click();');
//             $browser->pause(5000);
//             $browser->with('form div:nth-child(2)', function ($block2) {
//                 $block2->with('div.box-body div[id*="items_default"]', function ($block_item) {
//                     $block_item->click('ul li:nth-child(6) div.form-horizontal div:nth-child(2) ins.iCheck-helper');
//                 });
//             });
//             $browser->press('Submit')
//                 ->pause(2000);
//             $browser->visit('/admin/data/exmenttest_form/create')
//                 ->with('form div.embed-value-form.fields-group div:nth-child(6)', function ($rowDateAndTime) {
//                     $rowDateAndTime->assertSee('Date and Time');
//                 });
//         });
//     }
}
