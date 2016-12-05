mod.wizards.newContentElement.wizardItems.special {
	elements {
		contentwidgets {
			iconIdentifier = tx_contentwidgets-content-element
			title = LLL:EXT:contentwidgets/Resources/Private/Language/locallang.xlf:plugin_wizard_title
			description = LLL:EXT:contentwidgets/Resources/Private/Language/locallang.xlf:plugin_wizard_description
			tt_content_defValues {
				CType = contentwidgets_pi1
			}
		}
	}
	show := addToList(contentwidgets)
}