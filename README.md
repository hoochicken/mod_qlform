# mod_qlform

The form generator is based on the J! way of generating forms.

For input data can be sent via e-mail. Even captcha is included and can be inserted - if wished.

The form generator mod_qlforms is based on the Joomla! xml. Any parameter (calendar, text email etc.) is at your service. Form fields provided by Joomla! are here: <http://docs.joomla.org/Category:Standard_form_field_types>

## What about a coffee ..

I love coding. My extensions are for free. Wanna say thanks? You're welcome! 
<https://www.buymeacoffee.com/mareikeRiegel>


## Basic Options

Here you add the xml code. Use Joomla! xml style for form. Params U see here. As textarea doesn't allow , use [ and ] instead. So the form xml might look like that.

~~~
[form]
  [fieldset name="fieldset1"]
    [field name="name" type="text"  size="50" required="true" /]
    [field name="email" type="email" size="50" required="true" /]
    [field name="subject" type="text" size="50" /]
    [field name="message" type="textarea" cols="50" rows="20" required="true" /]
  [/fieldset]
[/form]
~~~

You must use a form tag and a fieldset tag. Fieldset tag MUST have a name, otherwise Joomla! can't display fields

## Additional fieldsets

You can add additional fieldsets with (or without) labels, e. g.

~~~
[form]
  [fieldset name="fieldset1" label="Personal data"]
    [field name="firstname" type="text"  size="50" required="true" /]
    [field name="lastname" type="email" size="50" required="true" /]
    [field name="subject" type="text" size="50" /]
    [field name="message" type="textarea" cols="50" rows="20" required="true" /
  [/fieldset]
  [fieldset name="fieldset2" label="Adress data"]
    [field name="street" type="text"  size="50" required="true" /]
    [field name="suburb" type="text" size="50" required="true" /]
    [field name="zip" type="text" size="50" /]
  [/fieldset]
[/form]
~~~

## Add additional form fields

* Generate an overriding layout. Generate copies of all files that you can find in modules/mod_qlcontact/tmpl/...
* generate overriding folder
  * templates/yourTemplate/html/mod_qlcontact/
* Copy all .php-files into overriding template folder, e. g.:
  * templates/yourTemplate/html/mod_qlcontact/default.php
  * templates/yourTemplate/html/mod_qlcontact/default_data.php
  * ...
* Go to templates/yourTemplate/html/mod_qlcontact/default_form.php
  * Add your additional fields here in html.
  * Add verifications via html (attribute required="required", e. g. <input type="text" name="product" required="required" id="product" />)
  * Add verifications via Javascript (for devs who can code Javascript)
  * A lot of testing until the e-mail and the data sent delivers the data you want;-)
* And: you're lucky*, that's it

* *Fortunately mod_qlform sends  a l l  post data given to it, no further coding needed, strike! :-)

## Send e-mail to multiple recipients

**(1) to all recipients**

* insert the e-mails into the e-mail recipient textarea
* separate by line breaks

~~~
info@ql.de
another-recipient@ql.de
~~~

**(2) use e-mail switch**

2.1 You can choose to send an e-mail to a specific recipient.
Imagine you have a form, that contains a dropdown list, let's say one as follows:

~~~
  [field name="room" label="Room" type="list" default="kitchen"]
    [option value="kitchen"]Kitchen with the cook[/option]
    [option value="bathroom"]Bathroom with funny and bunny[/option]
  [/field]
~~~

2.2 Define switch field

So you declare your "switch" field as `room`.

2.3 Now adjust e-mail recipient textarea

Add the mapping in the field

* field value of switch field
* colon :
* e-mails address(es)
* multiple e-mails are separated by ;

~~~
kitchen:cook@ql.de
bathroom:funny@ql.de;bunny@ql.de
FIELD_VALUE:EMAIL1;EMAIL2;EMAIL3
~~~

## Standard form field types

All form field types you find in the Joomla! docs:

http://docs.joomla.org/Category:Standard_form_field_types
http://docs.joomla.org/Standard_form_field_and_parameter_types

## Wellknown error message "invalid form"

In case you get the error message "invalid form" while saving. This refers not (!) to the xml in the textarea field. Just add a valid e-mail address in the e-mail address field.

By the way, this is why the param section "e-mail settings" is marked with an asterisk (*).

## Display mod_qlform in article

To display a mod_qlform or any other module in an article, do as follows:

* generate a mod_qlform; give it the position "qlform1"
* go to your article; enter {'loadposition qlform1} (without ' of course) into it
* that works already!
*  optimization: go to mod_qlform, change "Menu Assignement", activate module only for that very menu item, you call the article with (<-not neccassary, but nice:-)

Explanation: Joomla! has a plugin called up with the {'loadposition XXX} command (without ' of course). For "XXX" set the name for the module position you attach the module to; I always use something meaningful like "qlform1" or "contactform" a. s. o. You can call any module with this plugin command.

(Addition: Alternatively you could use the plg_qlmodule; that would make sense, if you have a bunch of forms that look all alike, but shall be sent to different e-mail recipient. For one or two forms the Joomla! core plugin is definately enough:-)

## Adding a text within form

Sometimes you wish to have an explaining text above or below a field. This can easily be done with a spacer field.

Just add the following tags within you xml:

~~~
[field type="spacer" label="This is an explaining text concerning the input below" description="This description text would show up when hovering the text"][/field]
~~~

More about spacers you find here: http://docs.joomla.org/Spacer_form_field_type

## Using the redirect function

* Create article like "Thank you very much for your message bla bla"
* Create "Shadow menu", it means a menu, that you will not display on website
* Create Menu entry in shadow menu link "thank you very much"
  * save
  * copy the url (here: "index.php/thank-you-very-much")
  * cancel
* Go to mod_qlform administration:
  * redirctions: use redirection: "yes"
  * redirctions: redirection: "/index.php/thank-you-very-much"
  * important!: leading "/" in front of "index.php"
  * save
* That should do it;-)

Explanation: After sending the mail (or doing whatever to the data) mod_qlform redirects to new page. This page is identified by it's url. As we want wo have a nice url ("/index.php/blabla") we create a menu entry in a shadow menu. Thus we avoid an seo unfriendly url like "indep.php?option=com_components&...". As it is only a shadow entry, it will be displayed nowhere.

## Cache and mod_qlform conflict

Symptom: Nothing happens; the captcha is static.

Probably the caching of the website is activated. Then the captcha pic won't be reloaded anymore.

**(1) For modules in a generic template module position**

Recommended setting:

* global configuration>caching:"no chaching"
* modules>mod_qlform>advanced>caching:"no"

**(2) For modules bound to an article via {'loadposition ...} or {'loadmodule ...} or so:**

Only one possible action: deactivate global configuration>caching:"no" and activate captcha

## Display form

Form settings>display form: "yes"

Redirection:"index.php/yourThankyoupage" (if you leave it blank, page will be empty aufter form submission)

## Changing input field size

(a) xml: add attribute size (sometimes not reliable)

Add the attribute "size" to your field - textareas require rows and cols attributes , e. g.:

~~~
[field type="text" size="20" ... /]
[field type="textarea" rows="20" cols="20" ... /]
~~~
Rating: Fast and easy, yet not reliable, as size attributes are interpreted differently by the browsers

(b) css: add stylesheets (always reliable, therefore recommended)

* Go to your stylesheets:
  * templates/yourTemplateName/css/yourCssFile.css or via Joomla!
* Extensions > Templates > Style > YourTemplateName > yourStylesheetFile.css
* Alter single, individual field:

~~~css
div.qlform #jform_name input {background:green; color:yellow;} /*input of field 'name'*/
~~~
If you want whole groups of inputs to look the same, call the via the fieldset they are in, like:

css
~~~css
#fieldset1 input {background:green; color:yellow;} /* all inputs in #fieldset1*/
~~~
Or call them via their class, that you have stated in the xml:

xml:
~~~
[field type="text" class="myGreenfields" ... /]
[field type="text" class="myGreenfields" ... /]
~~~
css:
~~~
div.myGreenFields  {background:green; color:yellow;} /* all inputs with class .myGreenFields*/
~~~

Rating: A little bit of work, but clean solution for every browser

## Using css styles on mod_qlform

mod_qlform generates regular html code. So use the normal css styles.
You can call the html code e. g. via following commands:

~~~css
div.qlform {} /*this div container around mod_qlform; depends on what name you gave it in the xml*/
div.qlform dt {} /*the dt container around the labels*/
div.qlform dd {} /*the dt container around the inputs*/
div.qlform dt.jform_name {} /*the very dt container of name field; depends on what name you gave it in the xml */
div.qlform dd.jform_name {} /*the very dt container of name field; depends on what name you gave it in the xml */
div.qlform input,div.qlform textarea, div.qlform button {} /*any button, textarea, input in mod_qlform*/
div.qlform .jform_name input {} /*input of field 'name'*/
~~~
Read also:

"Horizontal fields"
Important: User FireFox-Addon "Firebug", get it, you'll love it!

# Use Captcha

To use a captcha withing your module, do as follows:

**(1) Get captcha plugin extension**

* download any captcha plugin from http://extensions.joomla.org - of course I would recommend qlcaptcha;-)
* go to extensions> manage>install
* install the plugin

**(2) Configure captcha plugin**
 
* go to extension>plugins
* open theCaptchaPluginOfYourChoice
* adjust params of plugin
* save

**(3) Joomla! configuration**
* go to System>configuration
* find parameter "Captcha"
* select the captcha of your choice
* save

**(4) Module params**
* go to extensions>modules
* open mod_qlform
* go to captcha tab
* set parameter "Captcha" to "yes"
* save, DONE

## Horizontal fields by bootstrap

Try as follows (works in bootstrap template):

~~~
  [fieldset name="someName" class="row-fluid"]
    [field class="span6" name="someFieldname1" type="text" /]
    [field class="span6" name="someFieldname2" type="text" /]
  [/fieldset]
~~~

Explanation: In bootstrap the page is virually divided in 12 columns. The "span6" class ist 6 columns long; so span6 + span 6 adds up to 12; or span4+span2+span6. Each row is seated in a fieldset with a css class "row-fluid".

## Horizontal fields

It's all about css stuff; the basic css comand needed are as follows:

~~~css
float:left; /* the next element shall be to my left*/
clear:both; /* I will stand in the next line, I don't care what my pregoing elemnt wants  */
width: 150px; /*oder*/ width: 250px; /*often elements would like to float,
~~~
but are too big; so cut down the size a little*/

Helpful: FireFox-Addon "Firebug", get it, you'll love it!

2 Possibilities for horizontal fields

**(6.1) via css**

Every form field and every label has an own id or other html attribute. They are accessible via css.

Example xml:
~~~
[field name="zutaten" label="Checkboxes" type="checkboxes"]
[option value="1"]Salami[/option]
[option value="2"]Champignons[/option]
[option value="3"]Käse extra[/option]
[option value="4"]Noch mehr Käse[/option]
[/field]
~~~
css commands:
~~~css
#jform_zutaten li {float:left; display:block; width:200px; height:50px;}
#jform_zutaten li:nth-child(2n-1) {clear:both;} /*that means, every 1st,, 3rd, 5th etc. field ist displayed in a new line; works in FireFox and IE9; might not work on IE8.*/
~~~
Where to find css files? Go to the css files of your template. You find them here:

a) either via files: templates/yourTemplateName/css/styleSheetName.css
b) or via Joomla administration: Extensions>Template Manager>Templates>yourTemplateName Details and Files>styleSheetName.css

**(6.2) Via html**

(Rather for developers.) Generate an overriding layout. Generate copies of all files that you can find in modules/mod_qlform/tmpl/..., e. g.:

* templates/yourTemplate/html/mod_qlform/default.php
* templates/yourTemplate/html/mod_qlform/default_formPurehtml.php
* templates/yourTemplate/html/mod_qlform/default_formBootstrap.php
* ...

In the module params (tab "styles") you have chosen whether you want to have pure html or a bootstrap form. (Default setting ist pure html.) According to this param adjust one of the following files:

* templates/yourTemplate/html/mod_qlform/default_formPurehtml.php
* templates/yourTemplate/html/mod_qlform/default_formBootstrap.php

In the original files the form ist generate automatically via ->getFieldsetsWe're gonna change this now.

The field should be available as separate fields (that means e. g. not one "checkboxes" field, but several "checkbox" fields. Now you can generate labels an inputs via following commands:

~~~php
<?php
echo $form->getInput('fieldId');
echo $form->getLabel('fieldId');
~~~

These commands you can a. g. put into a table.

Example xml:
~~~
    [field name="salami" id="salami" label="Salami" type="checkbox" /]
    [field name="champignons" id="champignons" label="Champignons" type="checkbox" /]
    [field name="kaese-plus" id="kaese-plus" label="Käse extra" type="checkbox" /]
    [field name="kaese-doppelplus" id="kaese-doppelplus" label="Noch mehr Käse" type="checkbox" /]
~~~
The correspanding html

~~~html
<table>
<tr><?php echo $form->getInput('fieldId');?><td><?php echo $form->getInput('fieldId');?></td></tr>
<tr><?php echo $form->getInput('fieldId2');?><td><?php echo $form->getInput('fieldId2');?></td></tr>
</table>
~~~

## Prefilled data in form

mod_qlform can do this, if you use an override or - even better - an alternative layout.
Do as follows:

**(1) Create alternative layout**

* go to modules/mod_qlform/tmpl/
* copy all the files in this folder to templates/yourTemplateName/tmpl/mod_qlform/
* rename the files from "default" to "prefilled"
* go to "prefilled.php" and replace "default" by "prefilled"

**(2) Activate alternative layout for 2nd form (with the prefilled data)**

* J! backend: modules>prefilled module>advanced settings>
* choose here layout "prefilled"

**(3) Get data**

* go to templates/yourTemplateName/html/mod_qlform/prefilled_form.php
* add the following 3 bold lines after the "defined('_JEXEC') or die;" line, about line 10: 

~~~php
<?php
#either hardcoded data
$dataToBind=new stdClass;
$dataToBind->name='Hungry Hamster';
$dataToBind->street='Grain Street 14';
$dataToBind->city='Corn field';

# or post data
$dataToBind=new stdClass;
if (isset($_POST['jform'])) foreach ($_POST['jform'] as $k=>$v) $dataToBind->$k=$v;
~~~

**(4) Bind data to form**

* go to templates/yourTemplateName/html/mod_qlform/prefilled_form.php
* add following line behind your data generation

~~~php
<?php
$form->bind($dataToBind);
~~~
That's it, three lines!

## Save to database
 
**1. SETTINGS**

Use following settings (example here with recipe table):

* Basic Options > Save to Database : Yes
* Database > Database table : #__recipes [put here your table's name)
* Database > Add date created : Yes

**2. XML**

Say, we have a form for recipes people can send. XML looks like that:
~~~
[form]
[fieldset name="fieldset1"]
[field name="name" type="text"  size="50" required="true" /]
[field name="ingredients" type="textarea" size="50" cols="50" rows="20" required="true" /]
[field name="todo" type="textarea" size="50" cols="50" rows="20" required="true" /]
[/fieldset]
[/form]
~~~

**3. CREATE TABLE**

Create the table #__recipes in your database. This table has the following cols:

* id [autoincrement; important!]
* name
* ingredients
* todo
* created

**4. TROUBLE SHOOTING**

The module itself tells you, what it needs. Do as follows:

* Basic Options > Save to Database : Yes
* Basic Options > Message type : In module itself
* Database > Show db-form conflict : Yes
* Cascading forms > Add post data to form : No [default of elder versions was "yes", but might lead to problems here]

**5. READ ERROR MESSAGE AND ADJUST**

Now submit your form and see, what the form tells you. E. g. my qlform tells me now the following error message

"Table `fwj25_qlform_survey1` was not found in database `snoopy`. To store data, create new table in database.
For help check module parameters and read description of module.
mod_qlform : Db-form conflict:

* `name` column could not be found in database.
* `email` column could not be found in database.
* `subject` column could not be found in database.
* `message` column could not be found in database.

If intended, ignore message. (Tip: Set 'Display db-form conflict' to 'No' before going online.)"

Here you can see right away, what's missing. In my case, the table is not found (therefore no table cols are found, too:-).

## Preprocess data

Available since version 10.1.3

To preprocess data before storing to database or sending with e-mail etc, the data can be preprocessedd, To alter data before any further actions, the following actions have to be performed:

**module configuration**
* go to extensions > modules > mod_qlform
* set parameter "preprocess data" to "yes"

**file structrure**
* go to your file system
* go to modules/mod_qlform/php/classes/
* rename "modQlformPreprocessData.php-rename-me" to "modQlformPreprocessData.php"
* add your code to alter data
* Several methods whose names are pretty well self-explanatory are available. Their structure ist quite simple: they retrieve data and return it. Further more, there are class variables like the module params, and the form object.

~~~php
<?php
formDataBeforeValidation($data)
formAndFileDataBeforeValidation($data)
email($data)
database($data)
databaseExternal($data)
somethingElse($data)
completlyDifferent($data)
jmessage($data)
sendcopy($data)
addionally you find the neat method p() with the following code:
function p($data,$die=1)
{
echo '<pre>';print_r($data);echo '</pre>';
if(1==$die)die('kia');
}
~~~

## Add custom validation to qlform

There are two ways of adding a validation to qlform: (1) as single rules to certain form fields, (2) Global form validation.

## Custom validation for form field

Basically in J! you have some validation predifinde, just like "email", e. g.

~~~
[field name="emailFieldName" validation="email" /]
~~~

Instead of 'validate="email"' you might need another validation. Maybe you would like to check, if a date is formatted properly. You would do this as follows:

* Go to your xml and add the "addrulepath" attribute to the form tag:
    * `[form addrulepath="/templates/yourTemplate/html/mod_qlform/rules"]` - if you put in your template you might edit it via template administration - OR
    * `[form addrulepath="/modules/mod_qlform/php/rules"]` - only editable by ftp, no access by template, more secure
* Add the field into your form: `[field name="someFieldName" type="text" validation="Xxxxx" /]` - ideally you use a more transparent name like "datevalid";-)
* Add your rule file into this path, code is as follows:

~~~ php
<?php 
defined('_JEXEC') or die;
defined('JPATH_PLATFORM') or die;
class JFormRuleXxxxx extends JFormRule
     /**
     * Method to test      *
     * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
     * @param   mixed             $value    The form field value to validate.
     * @param   string            $group    The field name group control value. This acts as as an array container for the field.
     *                                      For example if the field has name="foo" and the group value is set to "bar" then the
     *                                      full field name would end up being "bar[foo]".
     * @param   JRegistry         $input    An optional JRegistry object with the entire data set to validate against the entire form.
     * @param   JForm             $form     The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid, false otherwise.
     *
     * @since   11.1
     */
     public function test(SimpleXMLElement $element, $value, $group = null, JRegistry $input = null, JForm $form = null)
    {
        try
        {
            //here check $value
            //if false throw exception
            if(1==2)throw new Exception(JText::_('Some error due to field Xxxxx occurred. Please insert correct syntax.'));
            //if true return true;
        }
        catch (Exception $e)
        {
            JFactory::getApplication()->enqueueMessage($e->getMessage());
            return false;
        }
    }
}
~~~

## Global validation to qlform

* Go to modules/qlform/php/classes
* Rename file modQlformValidation.php-rename-me to modQlformValidation.php
* Insert your validation code and all methods you want into this class
* return true or false
* DONE

## Custom field

* Go to your xml and add the "addrulepath" attribute to the form tag:
    * `[form addfieldpath="/templates/yourTemplate/html/mod_qlform/fields"]` - if you put in your template you might edit it via template administration - OR
    * `[form addfieldpath="/modules/mod_qlform/php/fields"]` - only editable by ftp, no access by template, more secure
* Add the field into your form: `[field name="someFieldName" type="Yyyyy" /]` - ideally you use a more transparent name like "commentary";-)
* Add your field file into this path, code is as follows:

~~~php
<?php 
defined('_JEXEC') or die;
jimport('joomla.html.html');
//import the necessary class definition for formfield
jimport('joomla.form.formfield');
class JFormFieldYyyyy extends JFormField
{
     /**
     * The form field type.
     *
     * @var  string
     * @since 1.6
     */
     protected $type = 'yyyyy'; //the form field type see the name is the same
     /**
     * Method to retrieve the lists that resides in your application using the API.
     *
     * @return array The field option objects.
     * @since 1.6
     */
     protected function getInput()
     {
        $options = array();
        $html='';
        $html.='<textarea style="width:400px;height:400px;" name="'.$this->name.'" id="'.$this->id.'">';
        $html.=$this->value;
        $html.='</textarea>';
        return $html;
     }
}
~~~

## Cascading form

Assuming we got 3 forms cascaded. All 3 forms gather the data; only the last one does something (like saving to database or sending an e-mail)

**1. Create articles and menu items**

Create 3 articles

1. create article "Form 1" with {loadposition formular1}
2. create article "Form 2" with {loadposition formular2}
3. create article "Form 3" with {loadposition formular3}

**2. Create menu items**

1. Menüpunkt pointing to article "Form 1", note down the url, e. g. /index.php/form-1
2. Menüpunkt pointing to article "Form 2", note down the url, e. g. /index.php/form-2
3. Menüpunkt pointing to article "Form 3", note down the url, e. g. /index.php/form-3

**3. Create modules and menu items**

* Formular 1
  * is seated on page /index.php/form-1
  * Settings:
  * position: formular1
  * Email>email (always required)
  * Cascading Forms>action > "/index.php/form-2" (without quotes)
* Formular 2
  * is seated on page /index.php/form-2
  * Settings:
  * position: formular1
  * Email>email (always required)
  * Cascading Forms>action > "/index.php/form-3" (without quotes)
  * Cascading Forms>addPostDataToForm > "yes"
* Formular 3
  * is seated on page /index.php/form-3
  * Form that finally sends data to database and/or send e-mail
  * Settings:
  * position: formular1
  * Email>email (always required)
  * Cascading Forms>action > NO Action, adressing itself with "#" (without quotes)
  * Cascading Forms>addPostDataToForm > "yes"
  * Basic Options>send email >"yes" (if desired)
  * Basic Options>safe to database >"yes" (if desired, watch out database settings)

## Changing translations via language override

For changes generate an override.ini. Works like that

* generate folder languages/overrides
* generate file languages/overrides/en-GB.override.ini
* Enter the line you wish in the the file, e. g.:
* MOD_QLFORM_SENDCOPY_LABEL="Here is your translation"
* You can look up all placeholders in the following file
* languages/en-GB/mod_qlform.ini
* Do not change this very file; change the override.ini

Explanation: In the module's template are only placeholders; if you change existing translations, you can adjust them by calling the placeholders in the override.ini.
Thus you avoid that translations are unwillingly overridden when upgrading the module

## Translating mod_qlform to new language

All language data are stored in the language fiels installed. Simple generate your own language file. Let's asume its the Norvegian language you'd like to translate to.

* go to folder "language/en-GB/"
* copy files "en-GB.mod_qlform.ini" und "en-GB.mod_qlform.sys.ini"
* go to folder "language/nn-NO/"
* put both files there
* rename files to "/nn-NO.mod_qlform.ini" und "/nn-NO.mod_qlform.sys.ini"
* open "/nn-NO.mod_qlform.ini"
* translate (tip: translate only what you really need, so just pick e. g. "Send copy to me":-), mind the syntax, so don't forget the opening =" and the ending"

You have a full translation for mod_qlform? Why use it once? Why use it alone?
Send it to me to add it to mod_qlform. Make it a real OpenSource!

## Strange things happen to e-mail-addresses

To suppress the cloaking change the setting in module's administration e-mail cloaking.

Explanation: Joomla! is "cloaking" e-mail-addresses to avoid spamming - when used within a component.

So when qlform is used within an article, an e-mail address in an input field is cloaked via javascript, and that gets alll wrong within a input field. So we switch e-mail-cloaking off and that's it:-)

## "Do something else", e. g. fileupload (for devs)

Mark: If you are a developer, if you can code, here is the "how to":-)
If you are not, maybe you try to get the component "jdownloads" or some other form module.

* Override templates files 
  * go to modules/mod_qlform/tmpl
  * copy all files in this folder
  * create folder templates/yourTemplate/html/mod_qlform
  * insert copied files here
  * modifytemplates/yourTemplate/html/mod_qlform/default_form.php: add 'enctype="multipart/form-data"' in form tag
* modify module and doSomethingElse
  * set params "do Something Else" to "yes"
  * rename "modules/mod_qlform/classes/modelQlSomethingElse.php-rename-me-when-you-use-me" to "modules/mod_qlform/classes/modelQlSomethingElse.php"
  * add own code in modules/mod_qlform/classes/modelQlSomethingElse.php; here you can add saving files etc.

## "Do something else", e. g. html e-mail

* backend>modules>mod_qlform>basic settings>"do Something else" >"Yes"
* activate "modelQlSomethingElse.php", thus read the pregoing paragraph about doing something else
* copy all method (functions within class "modQlMailer") from "modelQlMailer.php"
* paste all qlmailer methods into the class "modelQlSomethingElse" in "modelQlSomethingElse.php"
* add the following lines into the doSomethingElse() method:

~~~php
<?php
$recipient=preg_split("?\n?",$this->params->get('recipient'));
$subject=$this->params->get('subject');
$data=$this->data;
foreach ($recipient as $k=>$to)
{
$this->mail($to, $subject, $data);
}
~~~

* adjust method generateMail() to your html needs

I haven't tried this very code yet, so there might be a syntax mistakes in it; nevertheles it should be clear, how it works:-) Good luck!

## "Do something else" several times and different things

There are 2 different ways of doing something else, if you want to do something else and something completely different and other things I might not guess.

**(a) "hardcoded"**
All module data - including the id - is passed to SomethingElse(); now just generate a "fork" like

~~~php
<?php
function SomethingElse()
{
if (132==$this->module->id) $this->doThisVerySomething();
if (133==$this->module->id OR 142==$this->module->id ) $this->doSomethingCompletlyDifferent();
return true;
}
~~~

I don't know, if the syntax is really proper (no editor here), but I think you get what I mean:-)

**(b) more flexible (recommended)**

Use the "note" field in your module give some extra information, e. g. emailSender or sms.
This you can add new modules with the already existing functionality - without having to adjust the source code.

~~~php
<?php
function SomethingElse()
{
$note=$this->getNote($this->module->id);   
if ('sms'==$note) $this->sendSMS();
if ('emailSender'=$note) $this->sendEmail();
if ('databaseSpecial'=$note) $this->databaseSpecial();
return true;
}

function getModuleNote($moduleId)
{
$db=JFactory::getDbo();
$db->setQuery('SELECT `note` FROM `#__modules` WHERE `id`=\''.$moduleId.'\'');
return trim($db->loadObject()->note);
/*I don't know, if the syntax is proper (no editor here), but I think you get what I mean:-), you find how to do this query on Joomla! dev site*/
}

function sendSMS() {...}
function sendEmail() {...}
function databaseSpecial() {...}
~~~

Of course you could also perform several actions on only one module by using a if (preg_match('/sms/',$note) command or by $arrTasks=preg_split('/ /',$note); plus foreach ($arrTasks ...)

## Add own html source code

* Create an override of the mod_qlform
  * generate folder templates/yourTemplate/html/mod_qlform/
  * copy all file you can find in modules/mod_qlform/tmpl to the generated folder
  * now work with these files
* Manipulate overwriting file templates/yourTemplate/html/mod_qlform/default_form.php 
  * add you html code here, e. g. table etc.
  * strip that foreach stuff
  * Getting field stuff with following commands 

~~~php
<?php getInput('fieldId'); ?>
<?php getLabel('fieldId'); ?>

// around the label you can add your links
<a href="http://www.ql.de"><?php getInput('fieldId'); ?>
~~~

In this file you can do everything you like and know about html, css and forms. Just make sure, that everything works afterwards and that you haven't forgotten a maybe very important field.

## Add own javsacript validation

* Generate an override (see paragraph "Add own html source code")
* add your own javascript code
  * either in the override template files directly
  * or via a file called within the override. Use the addScript() method to call the script (see http://docs.joomla.org/JDocument::addScript/11.1)

## Set e-mail sender address as reply-to

* go to extensions>modules>mod_qlform
* click on tab "Email" settings
* Enter into "Reply-to" field the very name of the field containing the inserted e-mail address.
* If youtr e-mail field ist called "email", set tghis walue into the input
* Save
* Done

## Error message "Mail-function could not be initalised."

This message means, that everything is all right with mod_qlform.
mod_qlform hands over all mail data to the J! mailer object ... and this object fails to send the mail.
There might be several reasons why J! cannot send the message.
I suppose that the server does not allow J! so send the e-mails.
Do as follows:

* go to configuration> server->mailing/e-mail setting
* ask your provider for the parameters to be set in the fieldset "Mailing"; especially the smtp setting and the port.
* Then it should work.

Good luck!

## Sendcopy-Feature: use placeholders

Within the "send copy" params you can add a so-called pretext. This pretext will be displayed within the copy mail to the person who used the form.

In the pretext you can use placeholders like {*salutation*} or {*secondname*}. The words within the {*...*} correspond with the field names used in the xml. A pretext like:

~~~text
Dear {*salutation*} {*secondname*}, bla bla
~~~

generates a sendcopy e-mail like this:

~~~text
Dear Ms. Greedberg, bla bla
~~~

Enjoy:-)

## Add link in label

Use {{ and }} instead of < and >. So you can generate a link as follows:

~~~text
[field name="someName" label="Label with {{a href='http://google.de'}}link to Google{{/a}} and a line{{br/}}break ...Halleluja and amen!!" /]
~~~

The history: One nice supportee asked me about the link. And I got so annoyed by the stripped off tags during the tests, that I programmed a workaround. So thank you, Marianna, for stepping on my toes:-)
