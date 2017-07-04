//
//  ParentTableViewCell.h
//  You're DONE
//
//  Created by Leo Lorenz on 1/25/16.
//  Copyright © 2016 Leo Lorenz. All rights reserved.
//

#import <Foundation/Foundation.h>
#import <UIKit/UIKit.h>

@interface ParentTableCell : UITableViewCell

@property (weak, nonatomic) IBOutlet UILabel *nameLabel;
@property (weak, nonatomic) IBOutlet UIImageView *verifyImage;
@property (weak, nonatomic) IBOutlet UIButton *buttonImage;

@end
