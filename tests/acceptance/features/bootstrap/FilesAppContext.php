<?php

/**
 *
 * @copyright Copyright (c) 2017, Daniel Calviño Sánchez (danxuliu@gmail.com)
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

use Behat\Behat\Context\Context;

class FilesAppContext implements Context, ActorAwareInterface {

	use ActorAware;
	use FileListAncestorSetter;

	/**
	 * @return array
	 */
	public static function sections() {
		return [ "All files" => "files",
				 "Recent" => "recent",
				 "Favorites" => "favorites",
				 "Shared with you" => "sharingin",
				 "Shared with others" => "sharingout",
				 "Shared by link" => "sharinglinks",
				 "Tags" => "systemtagsfilter",
				 "Deleted files" => "trashbin" ];
	}

	/**
	 * @return Locator
	 */
	public static function mainViewForSection($section) {
		$sectionId = self::sections()[$section];

		return Locator::forThe()->id("app-content-$sectionId")->
				describedAs("Main view for section $section in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function currentSectionMainView() {
		return Locator::forThe()->xpath("//*[starts-with(@id, 'app-content-')  and not(contains(concat(' ', normalize-space(@class), ' '), ' hidden '))]")->
				describedAs("Current section main view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function detailsView() {
		return Locator::forThe()->id("app-sidebar")->
				describedAs("Details view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function closeDetailsViewButton() {
		return Locator::forThe()->css(".icon-close")->
				descendantOf(self::detailsView())->
				describedAs("Close details view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function fileNameInDetailsView() {
		return Locator::forThe()->css(".fileName")->
				descendantOf(self::detailsView())->
				describedAs("File name in details view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function fileDetailsInDetailsViewWithText($fileDetailsText) {
		return Locator::forThe()->xpath("//span[normalize-space() = '$fileDetailsText']")->
				descendantOf(self::fileDetailsInDetailsView())->
				describedAs("File details with text \"$fileDetailsText\" in details view in Files app");
	}

	/**
	 * @return Locator
	 */
	private static function fileDetailsInDetailsView() {
		return Locator::forThe()->css(".file-details")->
				descendantOf(self::detailsView())->
				describedAs("File details in details view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function inputFieldForTagsInDetailsView() {
		return Locator::forThe()->css(".systemTagsInfoView")->
				descendantOf(self::detailsView())->
				describedAs("Input field for tags in details view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function itemInInputFieldForTagsInDetailsViewForTag($tag) {
		return Locator::forThe()->xpath("//span[normalize-space() = '$tag']")->
				descendantOf(self::inputFieldForTagsInDetailsView())->
				describedAs("Item in input field for tags in details view for tag $tag in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function itemInDropdownForTag($tag) {
		return Locator::forThe()->xpath("//*[contains(concat(' ', normalize-space(@class), ' '), ' select2-result-label ')]//span[normalize-space() = '$tag']/ancestor::li")->
				descendantOf(self::select2Dropdown())->
				describedAs("Item in dropdown for tag $tag in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function checkmarkInItemInDropdownForTag($tag) {
		return Locator::forThe()->css(".checkmark")->
				descendantOf(self::itemInDropdownForTag($tag))->
				describedAs("Checkmark in item in dropdown for tag $tag in Files app");
	}

	/**
	 * @return Locator
	 */
	private static function select2Dropdown() {
		return Locator::forThe()->css("#select2-drop")->
				describedAs("Select2 dropdown in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function tabHeaderInDetailsViewNamed($tabHeaderName) {
		return Locator::forThe()->xpath("//li[normalize-space() = '$tabHeaderName']")->
				descendantOf(self::tabHeadersInDetailsView())->
				describedAs("Tab header named $tabHeaderName in details view in Files app");
	}

	/**
	 * @return Locator
	 */
	private static function tabHeadersInDetailsView() {
		return Locator::forThe()->css(".tabHeaders")->
				descendantOf(self::detailsView())->
				describedAs("Tab headers in details view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function tabInDetailsViewNamed($tabName) {
		return Locator::forThe()->xpath("//div[@id=//*[contains(concat(' ', normalize-space(@class), ' '), ' tabHeader ') and normalize-space() = '$tabName']/@data-tabid]")->
				descendantOf(self::detailsView())->
				describedAs("Tab named $tabName in details view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function loadingIconForTabInDetailsViewNamed($tabName) {
		return Locator::forThe()->css(".loading")->
				descendantOf(self::tabInDetailsViewNamed($tabName))->
				describedAs("Loading icon for tab named $tabName in details view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function shareLinkRow() {
		return Locator::forThe()->id("shareLink")->
				descendantOf(self::detailsView())->
				describedAs("Share link row in the details view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function shareLinkCheckbox() {
		// forThe()->checkbox("Enable") can not be used here; that would return
		// the checkbox itself, but the element that the user interacts with is
		// the label.
		return Locator::forThe()->xpath("//label[normalize-space() = 'Enable']")->
				descendantOf(self::shareLinkRow())->
				describedAs("Share link checkbox in the details view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function shareLinkMenuButton() {
		return Locator::forThe()->css(".share-menu > .icon")->
				descendantOf(self::shareLinkRow())->
				describedAs("Share link menu button in the details view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function shareLinkMenu() {
		return Locator::forThe()->css(".share-menu > .menu")->
				descendantOf(self::shareLinkRow())->
				describedAs("Share link menu in the details view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function copyUrlMenuItem() {
		return Locator::forThe()->xpath("//a[normalize-space() = 'Copy link']")->
				descendantOf(self::shareLinkMenu())->
				describedAs("Copy link menu item in the share link menu in the details view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function hideDownloadCheckbox() {
		// forThe()->checkbox("Hide download") can not be used here; that would
		// return the checkbox itself, but the element that the user interacts
		// with is the label.
		return Locator::forThe()->xpath("//label[normalize-space() = 'Hide download']")->
				descendantOf(self::shareLinkMenu())->
				describedAs("Hide download checkbox in the details view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function hideDownloadCheckboxInput() {
		return Locator::forThe()->checkbox("Hide download")->
				descendantOf(self::shareLinkMenu())->
				describedAs("Hide download checkbox input in the details view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function allowUploadAndEditingRadioButton() {
		// forThe()->radio("Allow upload and editing") can not be used here;
		// that would return the radio button itself, but the element that the
		// user interacts with is the label.
		return Locator::forThe()->xpath("//label[normalize-space() = 'Allow upload and editing']")->
				descendantOf(self::shareLinkMenu())->
				describedAs("Allow upload and editing radio button in the details view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function passwordProtectCheckbox() {
		// forThe()->checkbox("Password protect") can not be used here; that
		// would return the checkbox itself, but the element that the user
		// interacts with is the label.
		return Locator::forThe()->xpath("//label[normalize-space() = 'Password protect']")->
				descendantOf(self::shareLinkMenu())->
				describedAs("Password protect checkbox in the details view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function passwordProtectField() {
		return Locator::forThe()->css(".linkPassText")->descendantOf(self::shareLinkMenu())->
				describedAs("Password protect field in the details view in Files app");
	}

	/**
	 * @return Locator
	 */
	public static function passwordProtectWorkingIcon() {
		return Locator::forThe()->css(".linkPassMenu .icon-loading-small")->descendantOf(self::shareLinkMenu())->
				describedAs("Password protect working icon in the details view in Files app");
	}

	/**
	 * @Given I close the details view
	 */
	public function iCloseTheDetailsView() {
		$this->actor->find(self::closeDetailsViewButton(), 10)->click();
	}

	/**
	 * @Given I open the input field for tags in the details view
	 */
	public function iOpenTheInputFieldForTagsInTheDetailsView() {
		$this->actor->find(self::fileDetailsInDetailsViewWithText("Tags"), 10)->click();
	}

	/**
	 * @Given I open the :tabName tab in the details view
	 */
	public function iOpenTheTabInTheDetailsView($tabName) {
		$this->actor->find(self::tabHeaderInDetailsViewNamed($tabName), 10)->click();
	}

	/**
	 * @Given I share the link for :fileName
	 */
	public function iShareTheLinkFor($fileName) {
		$this->actor->find(FileListContext::shareActionForFile(self::currentSectionMainView(), $fileName), 10)->click();

		$this->actor->find(self::shareLinkCheckbox(), 5)->click();
	}

	/**
	 * @Given I write down the shared link
	 */
	public function iWriteDownTheSharedLink() {
		$this->showShareLinkMenuIfNeeded();

		$this->actor->find(self::copyUrlMenuItem(), 2)->click();

		// Clicking on the menu item copies the link to the clipboard, but it is
		// not possible to access that value from the acceptance tests. Due to
		// this the value of the attribute that holds the URL is used instead.
		$this->actor->getSharedNotebook()["shared link"] = $this->actor->find(self::copyUrlMenuItem(), 2)->getWrappedElement()->getAttribute("data-clipboard-text");
	}

	/**
	 * @When I check the tag :tag in the dropdown for tags in the details view
	 */
	public function iCheckTheTagInTheDropdownForTagsInTheDetailsView($tag) {
		$this->iSeeThatTheTagInTheDropdownForTagsInTheDetailsViewIsNotChecked($tag);

		$this->actor->find(self::itemInDropdownForTag($tag), 10)->click();
	}

	/**
	 * @When I uncheck the tag :tag in the dropdown for tags in the details view
	 */
	public function iUncheckTheTagInTheDropdownForTagsInTheDetailsView($tag) {
		$this->iSeeThatTheTagInTheDropdownForTagsInTheDetailsViewIsChecked($tag);

		$this->actor->find(self::itemInDropdownForTag($tag), 10)->click();
	}

	/**
	 * @When I set the download of the shared link as hidden
	 */
	public function iSetTheDownloadOfTheSharedLinkAsHidden() {
		$this->showShareLinkMenuIfNeeded();

		$this->iSeeThatTheDownloadOfTheLinkShareIsShown();

		$this->actor->find(self::hideDownloadCheckbox(), 2)->click();
	}

	/**
	 * @When I set the download of the shared link as shown
	 */
	public function iSetTheDownloadOfTheSharedLinkAsShown() {
		$this->showShareLinkMenuIfNeeded();

		$this->iSeeThatTheDownloadOfTheLinkShareIsHidden();

		$this->actor->find(self::hideDownloadCheckbox(), 2)->click();
	}

	/**
	 * @When I set the shared link as editable
	 */
	public function iSetTheSharedLinkAsEditable() {
		$this->showShareLinkMenuIfNeeded();

		$this->actor->find(self::allowUploadAndEditingRadioButton(), 2)->click();
	}

	/**
	 * @When I protect the shared link with the password :password
	 */
	public function iProtectTheSharedLinkWithThePassword($password) {
		$this->showShareLinkMenuIfNeeded();

		$this->actor->find(self::passwordProtectCheckbox(), 2)->click();

		$this->actor->find(self::passwordProtectField(), 2)->setValue($password . "\r");
	}

	/**
	 * @Then I see that the current page is the Files app
	 */
	public function iSeeThatTheCurrentPageIsTheFilesApp() {
		PHPUnit_Framework_Assert::assertStringStartsWith(
				$this->actor->locatePath("/apps/files/"),
				$this->actor->getSession()->getCurrentUrl());

		$this->setFileListAncestorForActor(self::currentSectionMainView(), $this->actor);
	}

	/**
	 * @Then I see that the details view is open
	 */
	public function iSeeThatTheDetailsViewIsOpen() {
		// The sidebar always exists in the DOM, so it has to be explicitly
		// waited for it to be visible instead of relying on the implicit wait
		// made to find the element.
		if (!WaitFor::elementToBeEventuallyShown(
				$this->actor,
				self::detailsView(),
				$timeout = 10 * $this->actor->getFindTimeoutMultiplier())) {
			PHPUnit_Framework_Assert::fail("The details view is not open yet after $timeout seconds");
		}
	}

	/**
	 * @Then I see that the details view is closed
	 */
	public function iSeeThatTheDetailsViewIsClosed() {
		if (!WaitFor::elementToBeEventuallyNotShown(
				$this->actor,
				self::detailsView(),
				$timeout = 10 * $this->actor->getFindTimeoutMultiplier())) {
			PHPUnit_Framework_Assert::fail("The details view is not closed yet after $timeout seconds");
		}
	}

	/**
	 * @Then I see that the file name shown in the details view is :fileName
	 */
	public function iSeeThatTheFileNameShownInTheDetailsViewIs($fileName) {
		PHPUnit_Framework_Assert::assertEquals(
				$this->actor->find(self::fileNameInDetailsView(), 10)->getText(), $fileName);
	}

	/**
	 * @Then I see that the input field for tags in the details view is shown
	 */
	public function iSeeThatTheInputFieldForTagsInTheDetailsViewIsShown() {
		PHPUnit_Framework_Assert::assertTrue(
				$this->actor->find(self::inputFieldForTagsInDetailsView(), 10)->isVisible());
	}

	/**
	 * @Then I see that the input field for tags in the details view contains the tag :tag
	 */
	public function iSeeThatTheInputFieldForTagsInTheDetailsViewContainsTheTag($tag) {
		PHPUnit_Framework_Assert::assertTrue(
				$this->actor->find(self::itemInInputFieldForTagsInDetailsViewForTag($tag), 10)->isVisible());
	}

	/**
	 * @Then I see that the input field for tags in the details view does not contain the tag :tag
	 */
	public function iSeeThatTheInputFieldForTagsInTheDetailsViewDoesNotContainTheTag($tag) {
		$this->iSeeThatTheInputFieldForTagsInTheDetailsViewIsShown();

		try {
			PHPUnit_Framework_Assert::assertFalse(
					$this->actor->find(self::itemInInputFieldForTagsInDetailsViewForTag($tag))->isVisible());
		} catch (NoSuchElementException $exception) {
		}
	}

	/**
	 * @Then I see that the tag :tag in the dropdown for tags in the details view is checked
	 */
	public function iSeeThatTheTagInTheDropdownForTagsInTheDetailsViewIsChecked($tag) {
		PHPUnit_Framework_Assert::assertTrue(
				$this->actor->find(self::checkmarkInItemInDropdownForTag($tag), 10)->isVisible());
	}

	/**
	 * @Then I see that the tag :tag in the dropdown for tags in the details view is not checked
	 */
	public function iSeeThatTheTagInTheDropdownForTagsInTheDetailsViewIsNotChecked($tag) {
		PHPUnit_Framework_Assert::assertTrue(
				$this->actor->find(self::itemInDropdownForTag($tag), 10)->isVisible());

		PHPUnit_Framework_Assert::assertFalse(
				$this->actor->find(self::checkmarkInItemInDropdownForTag($tag))->isVisible());
	}

	/**
	 * @When I see that the :tabName tab in the details view is eventually loaded
	 */
	public function iSeeThatTheTabInTheDetailsViewIsEventuallyLoaded($tabName) {
		if (!WaitFor::elementToBeEventuallyNotShown(
				$this->actor,
				self::loadingIconForTabInDetailsViewNamed($tabName),
				$timeout = 10 * $this->actor->getFindTimeoutMultiplier())) {
			PHPUnit_Framework_Assert::fail("The $tabName tab in the details view has not been loaded after $timeout seconds");
		}
	}

	/**
	 * @Then I see that the download of the link share is hidden
	 */
	public function iSeeThatTheDownloadOfTheLinkShareIsHidden() {
		$this->showShareLinkMenuIfNeeded();

		PHPUnit_Framework_Assert::assertTrue($this->actor->find(self::hideDownloadCheckboxInput(), 10)->isChecked());
	}

	/**
	 * @Then I see that the download of the link share is shown
	 */
	public function iSeeThatTheDownloadOfTheLinkShareIsShown() {
		$this->showShareLinkMenuIfNeeded();

		PHPUnit_Framework_Assert::assertFalse($this->actor->find(self::hideDownloadCheckboxInput(), 10)->isChecked());
	}

	/**
	 * @Then I see that the working icon for password protect is shown
	 */
	public function iSeeThatTheWorkingIconForPasswordProtectIsShown() {
		PHPUnit_Framework_Assert::assertNotNull($this->actor->find(self::passwordProtectWorkingIcon(), 10));
	}

	/**
	 * @Then I see that the working icon for password protect is eventually not shown
	 */
	public function iSeeThatTheWorkingIconForPasswordProtectIsEventuallyNotShown() {
		if (!WaitFor::elementToBeEventuallyNotShown(
				$this->actor,
				self::passwordProtectWorkingIcon(),
				$timeout = 10 * $this->actor->getFindTimeoutMultiplier())) {
			PHPUnit_Framework_Assert::fail("The working icon for password protect is still shown after $timeout seconds");
		}
	}

	/**
	 * @Given I share the link for :fileName protected by the password :password
	 */
	public function iShareTheLinkForProtectedByThePassword($fileName, $password) {
		$this->iShareTheLinkFor($fileName);
		$this->iProtectTheSharedLinkWithThePassword($password);
		$this->iSeeThatTheWorkingIconForPasswordProtectIsShown();
		$this->iSeeThatTheWorkingIconForPasswordProtectIsEventuallyNotShown();
	}

	private function showShareLinkMenuIfNeeded() {
		// In some cases the share menu is hidden after clicking on an action of
		// the menu. Therefore, if the menu is visible, wait a little just in
		// case it is in the process of being hidden due to a previous action,
		// in which case it is shown again.
		if (WaitFor::elementToBeEventuallyNotShown(
				$this->actor,
				self::shareLinkMenu(),
				$timeout = 2 * $this->actor->getFindTimeoutMultiplier())) {
			$this->actor->find(self::shareLinkMenuButton(), 10)->click();
		}
	}

}
