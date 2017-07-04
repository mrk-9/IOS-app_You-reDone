//
//  ViewController.h
//  You're DONE
//
//  Created by Leo Lorenz on 1/22/16.
//  Copyright © 2016 Leo Lorenz. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "Apimanager.h"

@interface ViewController : UIViewController<UITableViewDataSource, UITableViewDelegate>
{
    Apimanager *apimanager;
}

@property (strong, nonatomic) IBOutlet UIActivityIndicatorView *loadingIndicator;

@end

