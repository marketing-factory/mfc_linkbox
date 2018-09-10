# mfc_linkbox
Add the possibility to navigate in page tree before, after or upper

# How to add the plugin?
- install the extension
- activate them
- add the Static Include
- add the plugin of the page, you would like to display the menu (or add them to certain pages over typoscript [see below])


# Integrate the plugin over typoscript
- select you position on page, where the plugin should be displayed
- ```page.10.SELECTED_PART < plugin.tx_mfclinkbox_pi1``` (on CSC)
- add plugin.tx_mfclinkbox_pi1 into you selected part (e.g. ```<f:cObject typoscriptObjectPath="plugin.tx_mfclinkbox_pi1"/>```), if you are using FSC
