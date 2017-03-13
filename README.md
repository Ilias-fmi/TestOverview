# TestOverview

The main goal is to make accumulating results from multiple tests and exercises in arbitrary locations a lot more convenient. These test should be configurable in the test overview object; RBAC should be applied in the usual way, ie. a lecturer can only select his own tests/tests he or she has access to for inclusion in the overview. (The test overview should not be "hierarchical" object that has to "contain" the test it accumulates like a folder or category.)

The overview itself should present a table matrix of users (rows), test/exercise (end) results (percentages; columns) and a final mean value column. The matrix fields should have different background colors for passed (green), not passed (red). It remains white if no grade is given. TestOverview also presents a graphic view of the test and exercise results in form of column diagram. For exporting the results into another program TestOverview is able to generate a comma-separated values file which covers all results from added tests and exercises.

## Installation Instructions
1. Clone this repository to <ILIAS_DIRECTORY>/Customizing/global/plugins/Services/Repository/RepositoryObject/TestOverview

    1.1 Go to ilias root directory:

   ```bash
   1.2  mkdir -p Customizing/global/plugins/Services/Repository/RepositoryObject/
   1.3  cd Customizing/global/plugins/Services/Repository/RepositoryObject/
   1.4  git clone https://github.com/Ilias-fmi/TestOverview.git
   ```
   
2. Login to ILIAS with an administrator account (e.g. root)
3. Select **Plugins** from the **Administration** main menu drop down.
4. Search the **TestOverview** plugin in the list of plugin and choose **Update** and **Activate** from the **Actions** drop down.

##Manual
This manual shows the main functions of the plugin and explains how to use them.

###Importing Test/Exercieses
Go to the tab **Test Adminestration** and click on **Add tests to Overview**

![Picture not available](https://raw.githubusercontent.com/Ilias-fmi/TestOverview/ReadMe_update-1/readMe/TestImport.png)

Select your tests and click **Select**

![Picture not available](https://github.com/Ilias-fmi/TestOverview/blob/ReadMe_update-1/readMe/TestImport2.png)

Now your able to see the results in the subtab **TestOverview** 

![Picture not available](https://github.com/Ilias-fmi/TestOverview/blob/ReadMe_update-1/readMe/TO_table.png)
###Test/Exercise Diagrams
With TestOverview it is possible to creat diagrams of the average results. For a testdiagram go to the subtab **Diagram** in the tab **TestOverview**.

![Picture not available](https://github.com/Ilias-fmi/TestOverview/blob/ReadMe_update-1/readMe/TestDiagram_mit_pfeil.png)

For a exercisediagram you have to enter a granularity. If your granularity is to small the diagramm is set to 100 buckets as a max size.
###Export

### More Information
[Ilias Feature Wiki Entry](http://www.ilias.de/docu/goto_docu_wiki_1357_Test_Overview.html)
